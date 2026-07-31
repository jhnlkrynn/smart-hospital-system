<?php

namespace App\Services\Pharmacy;

use App\Enums\MedicationStatus;
use App\Enums\PrescriptionItemStatus;
use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrescriptionService
{
    public function __construct(
        private readonly ReferenceNumberService $numbers,
        private readonly PrescriptionAllergyService $allergies,
        private readonly AuditLogService $audit,
    ) {}

    public function createFromConsultation(Consultation $consultation, array $data, User $actor): Prescription
    {
        return DB::transaction(function () use ($consultation, $data, $actor): Prescription {
            $doctor = $actor->employee;
            if (! $doctor || (int) $consultation->doctor_employee_id !== (int) $doctor->id) {
                throw ValidationException::withMessages(['consultation' => 'Only the assigned doctor may prescribe for this consultation.']);
            }

            $items = $data['items'] ?? [];
            if ($items === []) {
                throw ValidationException::withMessages(['items' => 'Add at least one medication item.']);
            }

            $prescription = Prescription::create([
                'prescription_number' => $this->numbers->prescriptionNumber(),
                'consultation_id' => $consultation->id,
                'appointment_id' => $consultation->appointment_id,
                'patient_id' => $consultation->patient_id,
                'doctor_employee_id' => $consultation->doctor_employee_id,
                'department_id' => $consultation->department_id,
                'status' => PrescriptionStatus::Draft,
                'valid_from' => $data['valid_from'] ?? now()->toDateString(),
                'valid_until' => $data['valid_until'] ?? now()->addDays(30)->toDateString(),
                'clinical_notes' => $data['clinical_notes'] ?? null,
                'patient_instructions' => $data['patient_instructions'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            foreach ($items as $item) {
                $this->createItem($prescription, $item, $actor);
            }

            $this->allergies->refresh($prescription);
            $this->audit->record($actor, 'created', 'prescriptions', $prescription, 'Created prescription '.$prescription->prescription_number);

            return ($data['finalize'] ?? false) ? $this->finalize($prescription->refresh(), $actor) : $prescription->refresh();
        });
    }

    public function finalize(Prescription $prescription, User $actor): Prescription
    {
        return DB::transaction(function () use ($prescription, $actor): Prescription {
            if (! $prescription->status->isEditable()) {
                throw ValidationException::withMessages(['status' => 'Only draft prescriptions can be finalized.']);
            }

            if (! $prescription->items()->where('status', PrescriptionItemStatus::Active->value)->exists()) {
                throw ValidationException::withMessages(['items' => 'Add at least one active prescription item before finalizing.']);
            }

            if ($this->allergies->hasBlockingWarnings($prescription)) {
                throw ValidationException::withMessages(['allergies' => 'Acknowledge or document override reasons for allergy warnings before finalizing.']);
            }

            $prescription->forceFill([
                'status' => PrescriptionStatus::Finalized,
                'finalized_at' => now(),
                'finalized_by' => $actor->id,
                'updated_by' => $actor->id,
            ])->save();

            $this->audit->record($actor, 'finalized', 'prescriptions', $prescription, 'Finalized prescription '.$prescription->prescription_number);

            return $prescription->refresh();
        });
    }

    public function cancel(Prescription $prescription, User $actor, string $reason): Prescription
    {
        if ($prescription->status->isTerminal()) {
            throw ValidationException::withMessages(['status' => 'This prescription is already closed.']);
        }

        $prescription->forceFill([
            'status' => PrescriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $actor->id,
            'cancellation_reason' => $reason,
            'updated_by' => $actor->id,
        ])->save();

        $this->audit->record($actor, 'cancelled', 'prescriptions', $prescription, 'Cancelled prescription '.$prescription->prescription_number);

        return $prescription->refresh();
    }

    public function markReviewed(Prescription $prescription, User $actor): Prescription
    {
        if (! $prescription->status->isFinalized()) {
            throw ValidationException::withMessages(['status' => 'Only finalized prescriptions can be reviewed by pharmacy.']);
        }

        $prescription->forceFill([
            'status' => PrescriptionStatus::Reviewed,
            'reviewed_at' => now(),
            'reviewed_by' => $actor->id,
            'updated_by' => $actor->id,
        ])->save();

        $this->audit->record($actor, 'reviewed', 'prescriptions', $prescription, 'Reviewed prescription '.$prescription->prescription_number);

        return $prescription->refresh();
    }

    private function createItem(Prescription $prescription, array $data, User $actor): void
    {
        $medication = Medication::query()->with(['dosageForm', 'strengthUnit', 'defaultRoute', 'defaultFrequency'])->findOrFail($data['medication_id']);
        if (! $medication->status instanceof MedicationStatus || ! $medication->status->canBePrescribed()) {
            throw ValidationException::withMessages(['medication_id' => $medication->display_name.' cannot be prescribed.']);
        }

        if ($medication->status->requiresSpecialPermission() && ! $actor->can('medications.manage-formulary')) {
            throw ValidationException::withMessages(['medication_id' => $medication->display_name.' is restricted and requires special formulary permission.']);
        }

        $prescription->items()->create([
            'medication_id' => $medication->id,
            'medication_number_snapshot' => $medication->medication_number,
            'generic_name_snapshot' => $medication->generic_name,
            'brand_name_snapshot' => $medication->brand_name,
            'dosage_form_snapshot' => $medication->dosageForm?->name,
            'strength_snapshot' => trim((string) $medication->strength_value.' '.($medication->strengthUnit?->symbol ?? '')),
            'dose_quantity' => $data['dose_quantity'] ?? null,
            'dose_unit_id' => $data['dose_unit_id'] ?? null,
            'route_id' => $data['route_id'] ?? $medication->default_route_id,
            'route_snapshot' => $medication->defaultRoute?->name,
            'frequency_id' => $data['frequency_id'] ?? $medication->default_frequency_id,
            'frequency_snapshot' => $medication->defaultFrequency?->name,
            'duration_value' => $data['duration_value'] ?? null,
            'duration_unit' => $data['duration_unit'] ?? null,
            'quantity' => $data['quantity'],
            'quantity_unit_id' => $data['quantity_unit_id'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'pharmacy_notes' => $data['pharmacy_notes'] ?? null,
            'status' => PrescriptionItemStatus::Active,
        ]);
    }
}

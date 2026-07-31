<?php

namespace App\Services\Consultation;

use App\Enums\AppointmentStatus;
use App\Enums\ConsultationStatus;
use App\Enums\QueueStatus;
use App\Models\Consultation;
use App\Models\PatientQueue;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Queue\QueueService;
use App\Services\ReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsultationService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly QueueService $queues,
        private readonly AuditLogService $audit,
    ) {}

    public function startFromQueue(PatientQueue $queue, User $actor): Consultation
    {
        $doctor = $actor->employee;

        if (! $doctor) {
            throw ValidationException::withMessages(['doctor' => 'Only doctor employee accounts can start consultations.']);
        }

        if ($queue->doctor_employee_id && (int) $queue->doctor_employee_id !== (int) $doctor->id) {
            throw ValidationException::withMessages(['queue' => 'This queue entry is assigned to another doctor.']);
        }

        if ($queue->status->isTerminal()) {
            throw ValidationException::withMessages(['queue' => 'Terminal queue records cannot be used to start a consultation.']);
        }

        if ($queue->consultation()->exists()) {
            throw ValidationException::withMessages(['queue' => 'A consultation already exists for this queue entry.']);
        }

        return DB::transaction(function () use ($queue, $actor, $doctor): Consultation {
            $queue = PatientQueue::query()->lockForUpdate()->findOrFail($queue->id);

            if ($queue->consultation()->exists()) {
                throw ValidationException::withMessages(['queue' => 'A consultation already exists for this queue entry.']);
            }

            $consultation = Consultation::create([
                'consultation_number' => $this->references->consultationNumber(),
                'queue_entry_id' => $queue->id,
                'appointment_id' => $queue->appointment_id,
                'patient_id' => $queue->patient_id,
                'doctor_employee_id' => $doctor->id,
                'department_id' => $queue->department_id,
                'status' => ConsultationStatus::InProgress,
                'started_at' => now(),
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            if ($queue->status !== QueueStatus::WithDoctor) {
                $this->queues->transition($queue, QueueStatus::WithDoctor, $actor, ['doctor_employee_id' => $doctor->id], 'Consultation started.');
            }

            if ($consultation->appointment) {
                $consultation->appointment->update(['status' => AppointmentStatus::InProgress, 'updated_by' => $actor->id]);
            }

            $this->audit->record($actor, 'consultation.started', 'consultations', $consultation, 'Consultation started.');

            return $consultation->refresh();
        });
    }

    public function update(Consultation $consultation, array $data, User $actor): Consultation
    {
        $this->ensureEditable($consultation);

        return DB::transaction(function () use ($consultation, $data, $actor): Consultation {
            $consultation->update($data + ['updated_by' => $actor->id]);
            $this->audit->record($actor, 'consultation.updated', 'consultations', $consultation, 'Consultation clinical note updated.');

            return $consultation->refresh();
        });
    }

    public function complete(Consultation $consultation, array $data, User $actor): Consultation
    {
        $this->ensureEditable($consultation);

        if (! $consultation->diagnoses()->exists()) {
            throw ValidationException::withMessages(['diagnoses' => 'At least one diagnosis is required before completing the consultation.']);
        }

        return DB::transaction(function () use ($consultation, $data, $actor): Consultation {
            $consultation = Consultation::query()->lockForUpdate()->findOrFail($consultation->id);
            $consultation->update($data + [
                'status' => ConsultationStatus::Completed,
                'completed_at' => now(),
                'completed_by' => $actor->id,
                'updated_by' => $actor->id,
                'is_patient_visible' => true,
                'patient_summary' => $data['patient_summary'] ?? $consultation->patient_summary ?? $this->summaryFrom($data),
            ]);

            if ($consultation->queue && ! $consultation->queue->status->isTerminal()) {
                $this->queues->transition($consultation->queue, QueueStatus::Completed, $actor, notes: 'Consultation completed.');
            }

            if ($consultation->appointment && ! $consultation->appointment->status->isTerminal()) {
                $consultation->appointment->update(['status' => AppointmentStatus::Completed, 'updated_by' => $actor->id]);
            }

            $this->audit->record($actor, 'consultation.completed', 'consultations', $consultation, 'Consultation completed.');

            return $consultation->refresh();
        });
    }

    public function cancel(Consultation $consultation, array $data, User $actor): Consultation
    {
        if ($consultation->status->isFinalized()) {
            throw ValidationException::withMessages(['consultation' => 'Completed consultations cannot be cancelled.']);
        }

        $consultation->update([
            'status' => ConsultationStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $actor->id,
            'reopen_reason' => $data['reason'] ?? null,
            'updated_by' => $actor->id,
        ]);

        $this->audit->record($actor, 'consultation.cancelled', 'consultations', $consultation, 'Consultation cancelled.');

        return $consultation->refresh();
    }

    public function reopen(Consultation $consultation, string $reason, User $actor): Consultation
    {
        if (! $consultation->status->isFinalized()) {
            throw ValidationException::withMessages(['consultation' => 'Only completed consultations can be reopened.']);
        }

        $consultation->update([
            'status' => ConsultationStatus::Reopened,
            'reopened_at' => now(),
            'reopened_by' => $actor->id,
            'reopen_reason' => $reason,
            'is_patient_visible' => false,
            'updated_by' => $actor->id,
        ]);

        $this->audit->record($actor, 'consultation.reopened', 'consultations', $consultation, 'Consultation reopened with reason recorded.');

        return $consultation->refresh();
    }

    public function ensureDoctorOwns(Consultation $consultation, User $actor): void
    {
        if ((int) $consultation->doctor_employee_id !== (int) $actor->employee?->id) {
            abort(403);
        }
    }

    private function ensureEditable(Consultation $consultation): void
    {
        if (! $consultation->status->isEditable()) {
            throw ValidationException::withMessages(['consultation' => 'This consultation is finalized and read-only.']);
        }
    }

    private function summaryFrom(array $data): string
    {
        return trim(implode("\n\n", array_filter([
            $data['clinical_impression'] ?? null,
            $data['treatment_plan'] ?? null,
            $data['follow_up_instructions'] ?? null,
        ])));
    }
}

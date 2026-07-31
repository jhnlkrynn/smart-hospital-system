<?php

namespace App\Services\Laboratory;

use App\Enums\ConsultationStatus;
use App\Enums\LaboratoryPriority;
use App\Enums\LaboratoryRequestStatus;
use App\Enums\LaboratoryTestItemStatus;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryTest;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LaboratoryRequestService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly AuditLogService $audit,
    ) {}

    public function createFromConsultation(Consultation $consultation, array $data, User $actor): LaboratoryRequest
    {
        $doctor = $actor->employee;

        if (! $doctor || (int) $consultation->doctor_employee_id !== (int) $doctor->id) {
            throw ValidationException::withMessages(['consultation' => 'Laboratory requests can only be created from your assigned consultations.']);
        }

        if ($consultation->status === ConsultationStatus::Cancelled) {
            throw ValidationException::withMessages(['consultation' => 'Cancelled consultations cannot be used for laboratory requests.']);
        }

        $testIds = array_values(array_unique($data['laboratory_test_ids'] ?? []));
        if ($testIds === []) {
            throw ValidationException::withMessages(['laboratory_test_ids' => 'Select at least one laboratory test.']);
        }

        return DB::transaction(function () use ($consultation, $data, $actor, $doctor, $testIds): LaboratoryRequest {
            $tests = LaboratoryTest::query()->with('components')->whereIn('id', $testIds)->active()->get();

            if ($tests->count() !== count($testIds)) {
                throw ValidationException::withMessages(['laboratory_test_ids' => 'One or more selected tests are inactive or unavailable.']);
            }

            $expanded = $tests->flatMap(fn (LaboratoryTest $test) => $test->is_panel ? $test->components : collect([$test]))->unique('id')->values();

            $request = LaboratoryRequest::create([
                'request_number' => $this->references->laboratoryRequestNumber(),
                'consultation_id' => $consultation->id,
                'appointment_id' => $consultation->appointment_id,
                'patient_id' => $consultation->patient_id,
                'requesting_doctor_employee_id' => $doctor->id,
                'department_id' => $consultation->department_id,
                'priority' => LaboratoryPriority::from($data['priority'] ?? LaboratoryPriority::Routine->value),
                'status' => LaboratoryRequestStatus::SpecimenPending,
                'clinical_information' => $data['clinical_information'] ?? null,
                'provisional_diagnosis' => $data['provisional_diagnosis'] ?? null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'requested_at' => now(),
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            foreach ($expanded as $test) {
                LaboratoryRequestItem::create([
                    'laboratory_request_id' => $request->id,
                    'laboratory_test_id' => $test->id,
                    'test_code_snapshot' => $test->code,
                    'test_name_snapshot' => $test->name,
                    'result_type_snapshot' => $test->result_type,
                    'unit_snapshot' => $test->default_unit,
                    'specimen_type_id' => $test->specimen_type_id,
                    'priority' => $request->priority,
                    'status' => LaboratoryTestItemStatus::Pending,
                ]);
            }

            $this->audit->record($actor, 'laboratory_request.created', 'laboratory-requests', $request, 'Laboratory request created.');

            return $request->refresh()->load('items');
        });
    }

    public function cancel(LaboratoryRequest $request, string $reason, User $actor): LaboratoryRequest
    {
        if (! $request->status->isEditable()) {
            throw ValidationException::withMessages(['request' => 'This laboratory request cannot be cancelled at its current stage.']);
        }

        $request->update([
            'status' => LaboratoryRequestStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $actor->id,
            'cancellation_reason' => $reason,
            'updated_by' => $actor->id,
        ]);
        $request->items()->update(['status' => LaboratoryTestItemStatus::Cancelled->value, 'cancelled_at' => now(), 'cancelled_by' => $actor->id, 'cancellation_reason' => $reason]);
        $this->audit->record($actor, 'laboratory_request.cancelled', 'laboratory-requests', $request, 'Laboratory request cancelled.');

        return $request->refresh();
    }
}

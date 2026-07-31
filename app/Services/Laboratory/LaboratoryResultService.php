<?php

namespace App\Services\Laboratory;

use App\Enums\LaboratoryRequestStatus;
use App\Enums\LaboratoryResultType;
use App\Enums\LaboratoryTestItemStatus;
use App\Models\LaboratoryCriticalResultLog;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryResult;
use App\Models\LaboratoryResultAcknowledgment;
use App\Models\LaboratoryResultVersion;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LaboratoryResultService
{
    public function __construct(
        private readonly ReferenceRangeService $ranges,
        private readonly AuditLogService $audit,
    ) {}

    public function enter(LaboratoryRequestItem $item, array $data, User $actor): LaboratoryResult
    {
        $request = $item->laboratoryRequest;
        if (! $request->status->canEnterResults()) {
            throw ValidationException::withMessages(['request' => 'Results cannot be entered until an accepted specimen is in process.']);
        }

        return DB::transaction(function () use ($item, $request, $data, $actor): LaboratoryResult {
            $type = $item->result_type_snapshot;
            $numeric = $type === LaboratoryResultType::Numeric ? (float) $data['numeric_value'] : null;
            $range = $type === LaboratoryResultType::Numeric ? $this->ranges->resolve($item->laboratoryTest, $request->patient) : null;
            $flag = $this->ranges->flag($numeric, $range);

            $result = LaboratoryResult::updateOrCreate(
                ['laboratory_request_item_id' => $item->id],
                [
                    'laboratory_request_id' => $request->id,
                    'patient_id' => $request->patient_id,
                    'laboratory_test_id' => $item->laboratory_test_id,
                    'result_type' => $type,
                    'numeric_value' => $numeric,
                    'text_value' => $data['text_value'] ?? null,
                    'qualitative_value' => $data['qualitative_value'] ?? null,
                    'boolean_value' => $data['boolean_value'] ?? null,
                    'structured_value' => $data['structured_value'] ?? null,
                    'unit' => $data['unit'] ?? $item->unit_snapshot,
                    'reference_range_id' => $range?->id,
                    'reference_lower_bound' => $range?->lower_bound,
                    'reference_upper_bound' => $range?->upper_bound,
                    'critical_lower_bound' => $range?->critical_lower_bound,
                    'critical_upper_bound' => $range?->critical_upper_bound,
                    'text_reference' => $range?->text_reference,
                    'abnormal_flag' => $flag,
                    'is_critical' => $flag->isCritical(),
                    'technical_notes' => $data['technical_notes'] ?? null,
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'entered_at' => now(),
                    'entered_by' => $actor->id,
                ],
            );

            $item->update(['status' => LaboratoryTestItemStatus::ResultEntered]);
            $this->recalculateRequestStatus($request);

            if ($result->is_critical && ! LaboratoryCriticalResultLog::query()->where('laboratory_result_id', $result->id)->exists()) {
                LaboratoryCriticalResultLog::create([
                    'laboratory_result_id' => $result->id,
                    'laboratory_request_id' => $request->id,
                    'patient_id' => $request->patient_id,
                    'doctor_employee_id' => $request->requesting_doctor_employee_id,
                    'identified_at' => now(),
                    'identified_by' => $actor->id,
                ]);
            }

            $this->audit->record($actor, 'laboratory_result.entered', 'laboratory-results', $result, 'Laboratory result entered.');

            return $result->refresh();
        });
    }

    public function verify(LaboratoryResult $result, User $actor, ?string $notes = null): LaboratoryResult
    {
        if (! $result->entered_at) {
            throw ValidationException::withMessages(['result' => 'Result must be entered before verification.']);
        }

        $result->update(['verified_at' => now(), 'verified_by' => $actor->id, 'verification_notes' => $notes]);
        $result->item->update(['status' => LaboratoryTestItemStatus::Verified]);
        $this->recalculateRequestStatus($result->laboratoryRequest);
        $this->audit->record($actor, 'laboratory_result.verified', 'laboratory-results', $result, 'Laboratory result verified.');

        return $result->refresh();
    }

    public function release(LaboratoryResult $result, User $actor): LaboratoryResult
    {
        if (! $result->verified_at) {
            throw ValidationException::withMessages(['result' => 'Only verified results can be released.']);
        }

        $result->update(['released_at' => now(), 'released_by' => $actor->id, 'is_patient_visible' => true]);
        $result->item->update(['status' => LaboratoryTestItemStatus::Released]);
        $this->recalculateRequestStatus($result->laboratoryRequest);
        $this->audit->record($actor, 'laboratory_result.released', 'laboratory-results', $result, 'Laboratory result released.');

        return $result->refresh();
    }

    public function amend(LaboratoryResult $result, array $data, User $actor): LaboratoryResult
    {
        if (! $result->released_at) {
            throw ValidationException::withMessages(['result' => 'Only released results require amendment history.']);
        }

        return DB::transaction(function () use ($result, $data, $actor): LaboratoryResult {
            LaboratoryResultVersion::create([
                'laboratory_result_id' => $result->id,
                'version' => $result->version,
                'snapshot' => $result->only(['numeric_value', 'text_value', 'qualitative_value', 'boolean_value', 'structured_value', 'unit', 'abnormal_flag', 'is_critical', 'technical_notes']),
                'amendment_reason' => $data['amendment_reason'],
                'amended_by' => $actor->id,
                'amended_at' => now(),
            ]);

            $result->update([
                'numeric_value' => $data['numeric_value'] ?? $result->numeric_value,
                'text_value' => $data['text_value'] ?? $result->text_value,
                'qualitative_value' => $data['qualitative_value'] ?? $result->qualitative_value,
                'technical_notes' => $data['technical_notes'] ?? $result->technical_notes,
                'version' => $result->version + 1,
                'verified_at' => null,
                'verified_by' => null,
                'released_at' => null,
                'released_by' => null,
                'is_patient_visible' => false,
            ]);
            $result->item->update(['status' => LaboratoryTestItemStatus::ResultEntered]);
            $this->recalculateRequestStatus($result->laboratoryRequest);
            $this->audit->record($actor, 'laboratory_result.amended', 'laboratory-results', $result, 'Laboratory result amended with history.');

            return $result->refresh();
        });
    }

    public function acknowledge(LaboratoryResult $result, User $actor, ?string $notes = null): LaboratoryResultAcknowledgment
    {
        $doctor = $actor->employee;
        if (! $doctor || (int) $result->laboratoryRequest->requesting_doctor_employee_id !== (int) $doctor->id) {
            throw ValidationException::withMessages(['result' => 'Only the requesting doctor can acknowledge this result.']);
        }

        return LaboratoryResultAcknowledgment::firstOrCreate(
            ['laboratory_result_id' => $result->id, 'doctor_employee_id' => $doctor->id],
            ['acknowledged_by' => $actor->id, 'acknowledged_at' => now(), 'notes' => $notes],
        );
    }

    public function recalculateRequestStatus(LaboratoryRequest $request): void
    {
        $items = $request->items()->get();
        if ($items->isEmpty()) {
            return;
        }

        $statuses = $items->pluck('status');
        $status = match (true) {
            $statuses->every(fn ($status) => $status === LaboratoryTestItemStatus::Released) => LaboratoryRequestStatus::Released,
            $statuses->every(fn ($status) => $status === LaboratoryTestItemStatus::Verified || $status === LaboratoryTestItemStatus::Released) => LaboratoryRequestStatus::Verified,
            $statuses->every(fn ($status) => in_array($status, [LaboratoryTestItemStatus::ResultEntered, LaboratoryTestItemStatus::Verified, LaboratoryTestItemStatus::Released], true)) => LaboratoryRequestStatus::Completed,
            $statuses->contains(fn ($status) => in_array($status, [LaboratoryTestItemStatus::ResultEntered, LaboratoryTestItemStatus::Verified, LaboratoryTestItemStatus::Released], true)) => LaboratoryRequestStatus::PartiallyCompleted,
            default => $request->status,
        };

        $request->update(['status' => $status, 'released_at' => $status === LaboratoryRequestStatus::Released ? now() : $request->released_at]);
    }
}

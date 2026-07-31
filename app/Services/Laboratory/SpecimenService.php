<?php

namespace App\Services\Laboratory;

use App\Enums\LaboratoryRequestStatus;
use App\Enums\LaboratoryTestItemStatus;
use App\Enums\SpecimenStatus;
use App\Models\LaboratoryRequest;
use App\Models\LaboratorySpecimen;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SpecimenService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly AuditLogService $audit,
    ) {}

    public function collect(LaboratoryRequest $request, array $data, User $actor): LaboratorySpecimen
    {
        if (! $request->status->canCollectSpecimen()) {
            throw ValidationException::withMessages(['request' => 'Specimen cannot be collected for this request status.']);
        }

        return DB::transaction(function () use ($request, $data, $actor): LaboratorySpecimen {
            $itemIds = $data['item_ids'] ?? $request->items()->pluck('id')->all();
            $items = $request->items()->whereIn('id', $itemIds)->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['item_ids' => 'Select at least one request item.']);
            }

            $specimenTypeId = (int) $data['specimen_type_id'];
            if ($items->contains(fn ($item) => $item->specimen_type_id && (int) $item->specimen_type_id !== $specimenTypeId)) {
                throw ValidationException::withMessages(['specimen_type_id' => 'Specimen type is not compatible with one or more selected tests.']);
            }

            $specimen = LaboratorySpecimen::create([
                'accession_number' => $this->references->laboratoryAccessionNumber(),
                'laboratory_request_id' => $request->id,
                'patient_id' => $request->patient_id,
                'specimen_type_id' => $specimenTypeId,
                'status' => SpecimenStatus::Collected,
                'collected_at' => now(),
                'collected_by' => $actor->id,
                'collection_notes' => $data['collection_notes'] ?? null,
                'container_type' => $data['container_type'] ?? null,
                'specimen_volume' => $data['specimen_volume'] ?? null,
                'specimen_unit' => $data['specimen_unit'] ?? null,
                'barcode_value' => 'LAB-'.Str::uuid(),
            ]);
            $specimen->items()->sync($items->pluck('id')->all());
            $items->each->update(['status' => LaboratoryTestItemStatus::SpecimenCollected]);
            $request->update(['status' => LaboratoryRequestStatus::SpecimenCollected, 'updated_by' => $actor->id]);
            $this->audit->record($actor, 'laboratory_specimen.collected', 'laboratory-specimens', $specimen, 'Laboratory specimen collected.');

            return $specimen->refresh();
        });
    }

    public function accept(LaboratorySpecimen $specimen, User $actor): LaboratorySpecimen
    {
        $specimen->update(['status' => SpecimenStatus::Accepted, 'received_at' => now(), 'received_by' => $actor->id, 'accepted_at' => now(), 'accepted_by' => $actor->id]);
        $specimen->laboratoryRequest->update(['status' => LaboratoryRequestStatus::InProcess, 'received_at' => now(), 'updated_by' => $actor->id]);
        $specimen->items()->update(['status' => LaboratoryTestItemStatus::InProcess->value]);
        $this->audit->record($actor, 'laboratory_specimen.accepted', 'laboratory-specimens', $specimen, 'Laboratory specimen accepted.');

        return $specimen->refresh();
    }

    public function reject(LaboratorySpecimen $specimen, string $reason, User $actor): LaboratorySpecimen
    {
        $specimen->update(['status' => SpecimenStatus::Rejected, 'rejected_at' => now(), 'rejected_by' => $actor->id, 'rejection_reason' => $reason]);
        $specimen->laboratoryRequest->update(['status' => LaboratoryRequestStatus::RecollectionRequired, 'updated_by' => $actor->id]);
        $specimen->items()->update(['status' => LaboratoryTestItemStatus::Rejected->value]);
        $this->audit->record($actor, 'laboratory_specimen.rejected', 'laboratory-specimens', $specimen, 'Laboratory specimen rejected.');

        return $specimen->refresh();
    }
}

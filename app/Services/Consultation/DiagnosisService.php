<?php

namespace App\Services\Consultation;

use App\Enums\ConsultationStatus;
use App\Enums\DiagnosisStatus;
use App\Enums\DiagnosisType;
use App\Models\Consultation;
use App\Models\ConsultationDiagnosis;
use App\Models\DiagnosisCatalog;
use App\Models\PatientProblem;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiagnosisService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function add(Consultation $consultation, array $data, User $actor): ConsultationDiagnosis
    {
        if (! $consultation->status->isEditable()) {
            throw ValidationException::withMessages(['consultation' => 'Diagnoses cannot be changed after the consultation is finalized.']);
        }

        return DB::transaction(function () use ($consultation, $data, $actor): ConsultationDiagnosis {
            $catalog = isset($data['diagnosis_catalog_id'])
                ? DiagnosisCatalog::query()->find($data['diagnosis_catalog_id'])
                : null;

            if (! $catalog && blank($data['diagnosis_name'] ?? null)) {
                throw ValidationException::withMessages(['diagnosis_name' => 'Select a catalog diagnosis or enter a custom diagnosis.']);
            }

            $type = DiagnosisType::from($data['diagnosis_type']);

            if ($type === DiagnosisType::Primary) {
                $consultation->diagnoses()
                    ->where('diagnosis_type', DiagnosisType::Primary->value)
                    ->update(['diagnosis_type' => DiagnosisType::Secondary->value]);
            }

            $diagnosis = ConsultationDiagnosis::create([
                'consultation_id' => $consultation->id,
                'diagnosis_catalog_id' => $catalog?->id,
                'diagnosis_code_snapshot' => $catalog?->code ?? ($data['diagnosis_code'] ?? null),
                'diagnosis_name_snapshot' => $catalog?->name ?? $data['diagnosis_name'],
                'diagnosis_type' => $type,
                'diagnosis_status' => DiagnosisStatus::from($data['diagnosis_status'] ?? DiagnosisStatus::Active->value),
                'clinical_notes' => $data['clinical_notes'] ?? null,
                'onset_date' => $data['onset_date'] ?? null,
                'resolved_date' => $data['resolved_date'] ?? null,
                'is_patient_visible' => (bool) ($data['is_patient_visible'] ?? true),
                'sync_to_problem_list' => (bool) ($data['sync_to_problem_list'] ?? $type->syncsToProblemList()),
                'recorded_by' => $actor->id,
            ]);

            if ($diagnosis->sync_to_problem_list) {
                $this->syncProblem($consultation, $diagnosis, $actor);
            }

            $this->audit->record($actor, 'diagnosis.added', 'diagnoses', $diagnosis, 'Diagnosis added to consultation.');

            return $diagnosis->refresh();
        });
    }

    private function syncProblem(Consultation $consultation, ConsultationDiagnosis $diagnosis, User $actor): void
    {
        $query = PatientProblem::query()
            ->where('patient_id', $consultation->patient_id)
            ->whereNull('resolved_date')
            ->whereIn('status', [DiagnosisStatus::Active->value, DiagnosisStatus::Chronic->value]);

        if ($diagnosis->diagnosis_catalog_id) {
            $query->where('diagnosis_catalog_id', $diagnosis->diagnosis_catalog_id);
        } else {
            $query->where('problem_name', $diagnosis->diagnosis_name_snapshot);
        }

        if ($query->exists()) {
            return;
        }

        PatientProblem::create([
            'patient_id' => $consultation->patient_id,
            'diagnosis_catalog_id' => $diagnosis->diagnosis_catalog_id,
            'source_consultation_diagnosis_id' => $diagnosis->id,
            'problem_name' => $diagnosis->diagnosis_name_snapshot,
            'problem_code' => $diagnosis->diagnosis_code_snapshot,
            'status' => $diagnosis->diagnosis_status,
            'onset_date' => $diagnosis->onset_date,
            'notes' => 'Synced from consultation '.$consultation->consultation_number.'.',
            'is_chronic' => $diagnosis->diagnosis_status === DiagnosisStatus::Chronic,
            'is_patient_visible' => $diagnosis->is_patient_visible,
            'recorded_by' => $actor->id,
        ]);
    }
}

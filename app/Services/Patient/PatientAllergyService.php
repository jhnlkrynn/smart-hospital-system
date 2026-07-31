<?php

namespace App\Services\Patient;

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\User;
use App\Services\Audit\AuditLogService;

class PatientAllergyService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function add(Patient $patient, array $data, User $actor): PatientAllergy
    {
        $allergy = $patient->allergies()->create($data + ['recorded_by' => $actor->id]);
        $this->auditLog->record($actor, 'allergy_created', 'patients', $patient, 'Patient allergy recorded.', null, $allergy->toArray(), request());

        return $allergy;
    }
}

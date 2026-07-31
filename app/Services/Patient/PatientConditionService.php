<?php

namespace App\Services\Patient;

use App\Models\Patient;
use App\Models\PatientCondition;
use App\Models\User;
use App\Services\Audit\AuditLogService;

class PatientConditionService
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function add(Patient $patient, array $data, User $actor): PatientCondition
    {
        $condition = $patient->conditions()->create($data + ['recorded_by' => $actor->id]);
        $this->auditLog->record($actor, 'condition_created', 'patients', $patient, 'Patient condition recorded.', null, $condition->toArray(), request());

        return $condition;
    }
}

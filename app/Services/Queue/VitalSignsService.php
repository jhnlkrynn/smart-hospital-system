<?php

namespace App\Services\Queue;

use App\Models\PatientQueue;
use App\Models\User;
use App\Models\VitalSign;
use App\Services\Audit\AuditLogService;

class VitalSignsService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function record(PatientQueue $queue, User $actor, array $data): VitalSign
    {
        $height = isset($data['height_cm']) ? (float) $data['height_cm'] : null;
        $weight = isset($data['weight_kg']) ? (float) $data['weight_kg'] : null;
        $bmi = $height && $weight ? round($weight / (($height / 100) ** 2), 2) : null;

        $vital = VitalSign::create($data + [
            'queue_id' => $queue->id,
            'triage_record_id' => $queue->triageRecord?->id,
            'patient_id' => $queue->patient_id,
            'recorded_by' => $actor->id,
            'bmi' => $bmi,
            'measured_at' => $data['measured_at'] ?? now(),
        ]);

        $this->audit->record($actor, 'vital_signs.recorded', 'vital-signs', $vital, 'Vital signs recorded.', null, [
            'queue_id' => $queue->id,
            'bmi' => $bmi,
        ]);

        return $vital;
    }
}

<?php

namespace App\Services\Queue;

use App\Enums\QueueStatus;
use App\Models\PatientQueue;
use App\Models\TriageRecord;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class TriageService
{
    public function __construct(
        private readonly QueueService $queues,
        private readonly VitalSignsService $vitals,
        private readonly AuditLogService $audit,
    ) {}

    public function record(PatientQueue $queue, User $actor, array $triageData, array $vitalData = []): TriageRecord
    {
        return DB::transaction(function () use ($queue, $actor, $triageData, $vitalData): TriageRecord {
            $this->queues->transition($queue, QueueStatus::InTriage, $actor);
            $fallScore = (int) ($triageData['fall_risk_score'] ?? 0);
            $triage = TriageRecord::updateOrCreate(
                ['queue_id' => $queue->id],
                $triageData + [
                    'appointment_id' => $queue->appointment_id,
                    'patient_id' => $queue->patient_id,
                    'nurse_id' => $actor->id,
                    'fall_risk_level' => $fallScore >= 6 ? 'high' : ($fallScore >= 3 ? 'moderate' : 'low'),
                    'started_at' => $queue->triage_started_at ?? now(),
                    'completed_at' => now(),
                ]
            );

            if ($vitalData !== []) {
                $this->vitals->record($queue->refresh(), $actor, $vitalData + ['triage_record_id' => $triage->id]);
            }

            $this->queues->transition($queue->refresh(), QueueStatus::Triaged, $actor);
            $this->audit->record($actor, 'triage.recorded', 'triage', $triage, 'Triage record completed.');

            return $triage->refresh();
        });
    }
}

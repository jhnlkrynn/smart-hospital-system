<?php

namespace Database\Seeders;

use App\Enums\QueueStatus;
use App\Enums\TriageAcuity;
use App\Models\PatientQueue;
use App\Models\User;
use App\Services\Queue\TriageService;
use Illuminate\Database\Seeder;

class TriageSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::where('email', 'nurse@hospital.test')->first() ?? User::first();
        $queue = PatientQueue::query()->whereDoesntHave('triageRecord')->first();

        if (! $actor || ! $queue) {
            return;
        }

        app(TriageService::class)->record($queue, $actor, [
            'chief_complaint' => 'Fictional dizziness and headache.',
            'pain_scale' => 4,
            'pregnancy_flag' => false,
            'fall_risk_score' => 2,
            'acuity' => TriageAcuity::Routine,
            'allergies_reviewed' => true,
            'notes' => 'Fictional triage seed.',
        ], [
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
            'pulse_rate' => 78,
            'respiratory_rate' => 16,
            'temperature_c' => 36.8,
            'oxygen_saturation' => 98,
            'height_cm' => 170,
            'weight_kg' => 70,
        ]);

        $queue->refresh();
        if ($queue->status !== QueueStatus::Triaged) {
            $queue->update(['status' => QueueStatus::Triaged]);
        }
    }
}

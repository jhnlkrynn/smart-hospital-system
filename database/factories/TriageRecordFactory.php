<?php

namespace Database\Factories;

use App\Enums\TriageAcuity;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\TriageRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TriageRecord> */
class TriageRecordFactory extends Factory
{
    protected $model = TriageRecord::class;

    public function definition(): array
    {
        return [
            'queue_id' => PatientQueue::factory(),
            'patient_id' => Patient::factory(),
            'nurse_id' => User::factory(),
            'chief_complaint' => fake()->sentence(4),
            'pain_scale' => fake()->numberBetween(0, 10),
            'pregnancy_flag' => false,
            'fall_risk_score' => fake()->numberBetween(0, 5),
            'fall_risk_level' => 'low',
            'acuity' => TriageAcuity::Routine,
            'allergies_reviewed' => true,
            'started_at' => now(),
            'completed_at' => now(),
        ];
    }
}

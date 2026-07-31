<?php

namespace Database\Factories;

use App\Enums\PatientConditionStatus;
use App\Models\Patient;
use App\Models\PatientCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PatientCondition> */
class PatientConditionFactory extends Factory
{
    protected $model = PatientCondition::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'condition_name' => fake()->randomElement(['Hypertension', 'Asthma', 'Diabetes']),
            'status' => fake()->randomElement(PatientConditionStatus::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

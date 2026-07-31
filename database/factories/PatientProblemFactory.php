<?php

namespace Database\Factories;

use App\Enums\DiagnosisStatus;
use App\Models\Patient;
use App\Models\PatientProblem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PatientProblem> */
class PatientProblemFactory extends Factory
{
    protected $model = PatientProblem::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'problem_name' => fake()->words(3, true),
            'problem_code' => fake()->bothify('DX-####'),
            'status' => DiagnosisStatus::Active,
            'is_patient_visible' => true,
        ];
    }
}

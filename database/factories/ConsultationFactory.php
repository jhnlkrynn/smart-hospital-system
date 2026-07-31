<?php

namespace Database\Factories;

use App\Enums\ConsultationStatus;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Consultation> */
class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition(): array
    {
        return [
            'consultation_number' => 'CON-'.now('Asia/Manila')->format('Y').'-'.fake()->unique()->numberBetween(100000, 999999),
            'patient_id' => Patient::factory(),
            'doctor_employee_id' => Employee::factory(),
            'department_id' => Department::factory(),
            'status' => ConsultationStatus::InProgress,
            'started_at' => now(),
            'clinical_impression' => fake()->sentence(),
            'treatment_plan' => fake()->sentence(),
            'follow_up_instructions' => fake()->sentence(),
        ];
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'status' => ConsultationStatus::Completed,
            'completed_at' => now(),
            'is_patient_visible' => true,
            'patient_summary' => 'Condition explained with follow-up instructions.',
        ]);
    }
}

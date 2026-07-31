<?php

namespace Database\Factories;

use App\Enums\QueuePriority;
use App\Enums\QueueStatus;
use App\Enums\VisitType;
use App\Models\Department;
use App\Models\Patient;
use App\Models\PatientQueue;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PatientQueue> */
class PatientQueueFactory extends Factory
{
    protected $model = PatientQueue::class;

    public function definition(): array
    {
        return [
            'queue_number' => 'GEN-'.now('Asia/Manila')->format('Ymd').'-'.fake()->unique()->numberBetween(100, 999),
            'patient_id' => Patient::factory(),
            'department_id' => Department::factory(),
            'queue_date' => now('Asia/Manila')->toDateString(),
            'status' => QueueStatus::Waiting,
            'priority' => QueuePriority::Routine,
            'visit_type' => VisitType::WalkIn,
            'checked_in_at' => now(),
        ];
    }
}

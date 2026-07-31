<?php

namespace Database\Factories;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'appointment_number' => 'APT-'.now('Asia/Manila')->format('Y').'-'.fake()->unique()->numberBetween(100000, 999999),
            'patient_id' => Patient::factory(),
            'doctor_employee_id' => Employee::factory(),
            'department_id' => Department::factory(),
            'appointment_type_id' => AppointmentType::factory(),
            'appointment_date' => now('Asia/Manila')->addDays(fake()->numberBetween(1, 20))->toDateString(),
            'start_time' => '08:00',
            'end_time' => '08:30',
            'duration_minutes' => 30,
            'status' => AppointmentStatus::Confirmed,
            'source' => AppointmentSource::Staff,
            'reason_for_visit' => fake()->sentence(4),
        ];
    }
}

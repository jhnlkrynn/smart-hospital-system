<?php

namespace Database\Factories;

use App\Enums\ScheduleExceptionType;
use App\Models\DoctorScheduleException;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DoctorScheduleException> */
class DoctorScheduleExceptionFactory extends Factory
{
    protected $model = DoctorScheduleException::class;

    public function definition(): array
    {
        return [
            'doctor_employee_id' => Employee::factory(),
            'exception_date' => now('Asia/Manila')->addWeek()->toDateString(),
            'exception_type' => ScheduleExceptionType::Unavailable,
            'reason' => 'Fictional schedule exception.',
            'is_available' => false,
        ];
    }
}

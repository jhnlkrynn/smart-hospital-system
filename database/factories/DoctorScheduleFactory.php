<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DoctorSchedule> */
class DoctorScheduleFactory extends Factory
{
    protected $model = DoctorSchedule::class;

    public function definition(): array
    {
        return [
            'doctor_employee_id' => Employee::factory(),
            'day_of_week' => fake()->randomElement(DayOfWeek::cases()),
            'start_time' => '08:00',
            'end_time' => '12:00',
            'slot_duration_minutes' => 30,
            'break_start_time' => null,
            'break_end_time' => null,
            'maximum_appointments' => 8,
            'clinic_room' => fake()->bothify('RM-###'),
            'is_active' => true,
        ];
    }
}

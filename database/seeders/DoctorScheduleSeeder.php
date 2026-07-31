<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Employee::with('user.roles')->where('email', 'doctor@hospital.test')->first();

        if (! $doctor) {
            return;
        }

        foreach ([DayOfWeek::Monday, DayOfWeek::Tuesday, DayOfWeek::Wednesday, DayOfWeek::Thursday, DayOfWeek::Friday] as $day) {
            DoctorSchedule::updateOrCreate(
                ['doctor_employee_id' => $doctor->id, 'day_of_week' => $day->value],
                [
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'slot_duration_minutes' => 30,
                    'break_start_time' => '12:00',
                    'break_end_time' => '13:00',
                    'maximum_appointments' => 16,
                    'clinic_room' => $doctor->clinic_room ?? 'GEN-201',
                    'is_active' => true,
                    'effective_from' => now('Asia/Manila')->subMonth()->toDateString(),
                ]
            );
        }
    }
}

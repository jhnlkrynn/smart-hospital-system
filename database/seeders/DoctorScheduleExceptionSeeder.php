<?php

namespace Database\Seeders;

use App\Enums\ScheduleExceptionType;
use App\Models\DoctorScheduleException;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class DoctorScheduleExceptionSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Employee::where('email', 'doctor@hospital.test')->first();

        if (! $doctor) {
            return;
        }

        DoctorScheduleException::updateOrCreate(
            ['doctor_employee_id' => $doctor->id, 'exception_date' => now('Asia/Manila')->addWeeks(3)->toDateString()],
            [
                'exception_type' => ScheduleExceptionType::Unavailable,
                'reason' => 'Fictional professional leave.',
                'is_available' => false,
            ]
        );
    }
}

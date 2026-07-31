<?php

namespace Database\Seeders;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Employee;
use App\Models\Patient;
use App\Services\ReferenceNumberService;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Employee::where('email', 'doctor@hospital.test')->first();
        $type = AppointmentType::where('code', 'CONSULT')->first();
        $patients = Patient::query()->active()->take(6)->get();

        if (! $doctor || ! $type || $patients->isEmpty()) {
            return;
        }

        $references = app(ReferenceNumberService::class);
        $statuses = [
            AppointmentStatus::Pending,
            AppointmentStatus::Confirmed,
            AppointmentStatus::Approved,
            AppointmentStatus::Completed,
            AppointmentStatus::Cancelled,
            AppointmentStatus::NoShow,
        ];

        foreach ($patients as $index => $patient) {
            $date = now('Asia/Manila')->addDays($index + 1)->toDateString();
            $number = Appointment::where('patient_id', $patient->id)->whereDate('appointment_date', $date)->value('appointment_number') ?? $references->appointmentNumber();
            Appointment::updateOrCreate(
                ['appointment_number' => $number],
                [
                    'patient_id' => $patient->id,
                    'doctor_employee_id' => $doctor->id,
                    'department_id' => $doctor->department_id,
                    'appointment_type_id' => $type->id,
                    'appointment_date' => $date,
                    'start_time' => sprintf('%02d:00', 8 + $index),
                    'end_time' => sprintf('%02d:30', 8 + $index),
                    'duration_minutes' => 30,
                    'status' => $statuses[$index % count($statuses)],
                    'source' => $index % 2 === 0 ? AppointmentSource::Admin : AppointmentSource::PatientPortal,
                    'reason_for_visit' => 'Fictional appointment follow-up.',
                ]
            );
        }
    }
}

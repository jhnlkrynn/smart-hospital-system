<?php

namespace Tests\Unit\Services;

use App\Enums\AppointmentStatus;
use App\Enums\DayOfWeek;
use App\Enums\EmploymentStatus;
use App\Enums\ScheduleExceptionType;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleException;
use App\Models\Employee;
use App\Models\Patient;
use App\Models\User;
use App\Services\Appointment\DoctorAvailabilityService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_slots_respect_breaks_exceptions_existing_appointments_and_daily_maximum(): void
    {
        [$doctor, $patient] = $this->doctorAndPatient();
        $date = now('Asia/Manila')->next(DayOfWeek::Monday->carbonIso())->toDateString();

        DoctorSchedule::factory()->create([
            'doctor_employee_id' => $doctor->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'break_start_time' => '09:00',
            'break_end_time' => '09:30',
            'slot_duration_minutes' => 30,
            'maximum_appointments' => 3,
        ]);
        $type = AppointmentType::factory()->create(['default_duration_minutes' => 30]);
        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_employee_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_type_id' => $type->id,
            'appointment_date' => $date,
            'start_time' => '08:00',
            'end_time' => '08:30',
            'status' => AppointmentStatus::Approved,
        ]);
        Appointment::factory()->create([
            'patient_id' => Patient::factory()->create()->id,
            'doctor_employee_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_type_id' => $type->id,
            'appointment_date' => $date,
            'start_time' => '08:30',
            'end_time' => '09:00',
            'status' => AppointmentStatus::Cancelled,
        ]);

        $slots = app(DoctorAvailabilityService::class)->availableSlots($doctor, $date);

        $this->assertEquals(['08:30', '09:30'], $slots->pluck('start')->values()->all());

        DoctorScheduleException::factory()->create([
            'doctor_employee_id' => $doctor->id,
            'exception_date' => $date,
            'exception_type' => ScheduleExceptionType::Leave,
            'is_available' => false,
        ]);

        $this->assertCount(0, app(DoctorAvailabilityService::class)->availableSlots($doctor, $date));
    }

    private function doctorAndPatient(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $doctor = Employee::factory()->create([
            'user_id' => $doctorUser->id,
            'department_id' => Department::factory(),
            'employment_status' => EmploymentStatus::Active,
        ]);

        return [$doctor, Patient::factory()->create()];
    }
}

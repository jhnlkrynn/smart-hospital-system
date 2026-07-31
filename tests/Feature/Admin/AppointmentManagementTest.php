<?php

namespace Tests\Feature\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\DayOfWeek;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_appointment_and_double_booking_is_rejected(): void
    {
        [$admin, $doctor, $patient, $type, $date] = $this->setupBooking();

        $payload = [
            'patient_id' => $patient->id,
            'doctor_employee_id' => $doctor->id,
            'appointment_type_id' => $type->id,
            'appointment_date' => $date,
            'start_time' => '08:00',
            'reason_for_visit' => 'Fictional checkup',
        ];

        $this->actingAs($admin)->post(route('admin.appointments.store'), $payload)->assertRedirect();
        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseHas('appointments', ['department_id' => $doctor->department_id, 'status' => AppointmentStatus::Confirmed->value]);

        $this->actingAs($admin)->post(route('admin.appointments.store'), $payload)->assertSessionHasErrors(['start_time']);
    }

    public function test_admin_can_approve_cancel_and_reschedule(): void
    {
        [$admin, $doctor, $patient, $type, $date] = $this->setupBooking();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_employee_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_type_id' => $type->id,
            'appointment_date' => $date,
            'start_time' => '08:00',
            'end_time' => '08:30',
            'status' => AppointmentStatus::Pending,
        ]);

        $this->actingAs($admin)->post(route('admin.appointments.approve', $appointment))->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => AppointmentStatus::Approved->value]);

        $this->actingAs($admin)->post(route('admin.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '08:30',
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => AppointmentStatus::Rescheduled->value]);
        $this->assertDatabaseHas('appointments', ['parent_appointment_id' => $appointment->id]);
    }

    private function setupBooking(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('hospital-admin');
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $department = Department::factory()->create();
        $doctor = Employee::factory()->create(['user_id' => $doctorUser->id, 'department_id' => $department->id, 'employment_status' => 'active']);
        $patient = Patient::factory()->create();
        $type = AppointmentType::factory()->create(['default_duration_minutes' => 30, 'requires_approval' => false]);
        $date = now('Asia/Manila')->next(DayOfWeek::Monday->carbonIso())->toDateString();
        DoctorSchedule::factory()->create([
            'doctor_employee_id' => $doctor->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'slot_duration_minutes' => 30,
            'maximum_appointments' => 4,
        ]);

        return [$admin, $doctor, $patient, $type, $date];
    }
}

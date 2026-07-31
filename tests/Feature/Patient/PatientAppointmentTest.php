<?php

namespace Tests\Feature\Patient;

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

class PatientAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_book_only_for_self_and_view_own_appointments(): void
    {
        [$user, $doctor, $type, $date] = $this->setupPatient();
        $other = Patient::factory()->create();

        $this->actingAs($user)->post(route('patient.appointments.store'), [
            'patient_id' => $other->id,
            'doctor_employee_id' => $doctor->id,
            'appointment_type_id' => $type->id,
            'appointment_date' => $date,
            'start_time' => '08:00',
            'reason_for_visit' => 'Portal booking',
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', ['patient_id' => $user->patient->id]);
        $this->assertDatabaseMissing('appointments', ['patient_id' => $other->id]);

        $appointment = Appointment::first();
        $this->actingAs($user)->get(route('patient.appointments.show', $appointment))->assertOk();

        $otherAppointment = Appointment::factory()->create(['patient_id' => $other->id]);
        $this->actingAs($user)->get(route('patient.appointments.show', $otherAppointment))->assertForbidden();
    }

    public function test_patient_cannot_cancel_completed_appointment(): void
    {
        [$user, $doctor, $type, $date] = $this->setupPatient();
        $appointment = Appointment::factory()->create([
            'patient_id' => $user->patient->id,
            'doctor_employee_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_type_id' => $type->id,
            'appointment_date' => $date,
            'status' => AppointmentStatus::Completed,
        ]);

        $this->actingAs($user)->post(route('patient.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Cannot attend',
        ])->assertSessionHasErrors(['appointment']);
    }

    private function setupPatient(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('patient');
        Patient::factory()->create(['user_id' => $user->id]);
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $department = Department::factory()->create();
        $doctor = Employee::factory()->create(['user_id' => $doctorUser->id, 'department_id' => $department->id, 'employment_status' => 'active']);
        $type = AppointmentType::factory()->create(['default_duration_minutes' => 30, 'requires_approval' => true]);
        $date = now('Asia/Manila')->next(DayOfWeek::Monday->carbonIso())->toDateString();
        DoctorSchedule::factory()->create([
            'doctor_employee_id' => $doctor->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'slot_duration_minutes' => 30,
            'maximum_appointments' => 4,
        ]);

        return [$user, $doctor, $type, $date];
    }
}

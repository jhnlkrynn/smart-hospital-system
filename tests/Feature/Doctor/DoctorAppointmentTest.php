<?php

namespace Tests\Feature\Doctor;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_view_and_approve_only_assigned_appointments(): void
    {
        [$doctorUser, $doctor] = $this->doctor();
        [, $otherDoctor] = $this->doctor();
        $assigned = Appointment::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id, 'status' => AppointmentStatus::Pending]);
        $other = Appointment::factory()->create(['doctor_employee_id' => $otherDoctor->id, 'department_id' => $otherDoctor->department_id, 'status' => AppointmentStatus::Pending]);

        $this->actingAs($doctorUser)->get(route('doctor.appointments.show', $assigned))->assertOk();
        $this->actingAs($doctorUser)->get(route('doctor.appointments.show', $other))->assertForbidden();
        $this->actingAs($doctorUser)->post(route('doctor.appointments.approve', $assigned))->assertRedirect();

        $this->assertDatabaseHas('appointments', ['id' => $assigned->id, 'status' => AppointmentStatus::Approved->value]);
    }

    private function doctor(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $employee = Employee::factory()->create(['user_id' => $user->id, 'department_id' => Department::factory(), 'employment_status' => 'active']);

        return [$user, $employee];
    }
}

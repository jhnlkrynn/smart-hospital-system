<?php

namespace Tests\Feature\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\QueuePriority;
use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_can_check_in_appointment_once(): void
    {
        $nurse = $this->user('nurse');
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::Confirmed]);

        $this->actingAs($nurse)->post(route('admin.appointments.check-in', $appointment), [
            'is_senior_citizen' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'priority' => QueuePriority::SeniorCitizen->value,
        ]);
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => AppointmentStatus::CheckedIn->value]);

        $this->actingAs($nurse)->post(route('admin.appointments.check-in', $appointment))->assertSessionHasErrors(['appointment']);
    }

    public function test_walk_in_priority_and_call_next_order(): void
    {
        $nurse = $this->user('nurse');
        $department = Department::factory()->create(['code' => 'ER']);
        $routine = Patient::factory()->create();
        $emergency = Patient::factory()->create();

        $this->actingAs($nurse)->post(route('admin.queues.store'), [
            'patient_id' => $routine->id,
            'department_id' => $department->id,
        ])->assertRedirect();
        $this->actingAs($nurse)->post(route('admin.queues.store'), [
            'patient_id' => $emergency->id,
            'department_id' => $department->id,
            'is_emergency' => true,
        ])->assertRedirect();

        $this->actingAs($nurse)->post(route('admin.queues.call-next', $department))->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'patient_id' => $emergency->id,
            'status' => QueueStatus::Called->value,
            'priority' => QueuePriority::Emergency->value,
        ]);
    }

    private function user(string $role): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

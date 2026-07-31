<?php

namespace Tests\Feature\Doctor;

use App\Enums\QueueStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PatientQueue;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_complete_only_own_queue(): void
    {
        [$doctorUser, $doctor] = $this->doctor();
        [, $otherDoctor] = $this->doctor();
        $own = PatientQueue::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id, 'status' => QueueStatus::Triaged]);
        $other = PatientQueue::factory()->create(['doctor_employee_id' => $otherDoctor->id, 'department_id' => $otherDoctor->department_id, 'status' => QueueStatus::Triaged]);

        $this->actingAs($doctorUser)->post(route('doctor.queues.start', $own))->assertRedirect();
        $this->actingAs($doctorUser)->post(route('doctor.queues.complete', $own))->assertRedirect();
        $this->actingAs($doctorUser)->post(route('doctor.queues.start', $other))->assertForbidden();

        $this->assertDatabaseHas('queues', ['id' => $own->id, 'status' => QueueStatus::Completed->value]);
    }

    private function doctor(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $employee = Employee::factory()->create(['user_id' => $user->id, 'department_id' => Department::factory()]);

        return [$user, $employee];
    }
}

<?php

namespace Tests\Feature\Doctor;

use App\Enums\LaboratoryRequestStatus;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryTest;
use App\Models\SpecimenType;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_create_request_from_assigned_consultation(): void
    {
        [$user, $doctor] = $this->doctor();
        $consultation = Consultation::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id]);
        $test = LaboratoryTest::factory()->create();

        $this->actingAs($user)->post(route('doctor.consultations.laboratory-requests.store', $consultation), [
            'laboratory_test_ids' => [$test->id],
            'priority' => 'routine',
        ])->assertRedirect();

        $this->assertDatabaseHas('laboratory_requests', [
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'requesting_doctor_employee_id' => $doctor->id,
            'status' => LaboratoryRequestStatus::SpecimenPending->value,
        ]);
        $this->assertDatabaseHas('laboratory_request_items', ['laboratory_test_id' => $test->id]);
    }

    public function test_doctor_cannot_create_request_from_another_doctor_consultation_or_inactive_test(): void
    {
        [$user] = $this->doctor();
        [, $otherDoctor] = $this->doctor();
        $consultation = Consultation::factory()->create(['doctor_employee_id' => $otherDoctor->id, 'department_id' => $otherDoctor->department_id]);
        $inactive = LaboratoryTest::factory()->create(['is_active' => false]);

        $this->actingAs($user)->post(route('doctor.consultations.laboratory-requests.store', $consultation), [
            'laboratory_test_ids' => [$inactive->id],
            'priority' => 'routine',
        ])->assertSessionHasErrors('consultation');
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

<?php

namespace Tests\Feature\Doctor;

use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Medication;
use App\Models\MedicationFrequency;
use App\Models\MedicationRoute;
use App\Models\MedicationUnit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_create_and_finalize_prescription_for_assigned_consultation(): void
    {
        [$user, $doctor] = $this->doctor();
        $consultation = Consultation::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id]);
        $medication = Medication::factory()->create();
        $unit = MedicationUnit::factory()->create();
        $route = MedicationRoute::factory()->create();
        $frequency = MedicationFrequency::factory()->create();

        $this->actingAs($user)->post(route('doctor.consultations.prescriptions.store', $consultation), [
            'finalize' => true,
            'items' => [[
                'medication_id' => $medication->id,
                'dose_quantity' => 1,
                'dose_unit_id' => $unit->id,
                'route_id' => $route->id,
                'frequency_id' => $frequency->id,
                'duration_value' => 5,
                'duration_unit' => 'days',
                'quantity' => 10,
                'quantity_unit_id' => $unit->id,
                'instructions' => 'Take after meals.',
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'consultation_id' => $consultation->id,
            'status' => PrescriptionStatus::Finalized->value,
        ]);
        $this->assertDatabaseHas('prescription_items', ['medication_id' => $medication->id, 'quantity' => 10]);
    }

    public function test_doctor_cannot_prescribe_for_another_doctor_consultation(): void
    {
        [$user] = $this->doctor();
        [, $otherDoctor] = $this->doctor();
        $consultation = Consultation::factory()->create(['doctor_employee_id' => $otherDoctor->id, 'department_id' => $otherDoctor->department_id]);

        $this->actingAs($user)->post(route('doctor.consultations.prescriptions.store', $consultation), [
            'items' => [['medication_id' => Medication::factory()->create()->id, 'quantity' => 5]],
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

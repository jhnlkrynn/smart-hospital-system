<?php

namespace Tests\Feature\Doctor;

use App\Enums\AllergySeverity;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Medication;
use App\Models\PatientAllergy;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionAllergyWarningTest extends TestCase
{
    use RefreshDatabase;

    public function test_severe_allergy_warning_blocks_finalize_until_acknowledged(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Employee::factory()->create(['user_id' => $user->id, 'department_id' => Department::factory()]);
        $consultation = Consultation::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id]);
        $medication = Medication::factory()->create(['generic_name' => 'Amoxicillin']);
        PatientAllergy::factory()->create(['patient_id' => $consultation->patient_id, 'allergen' => 'Amoxicillin', 'severity' => AllergySeverity::Severe, 'is_active' => true]);

        $this->actingAs($user)->post(route('doctor.consultations.prescriptions.store', $consultation), [
            'finalize' => true,
            'items' => [['medication_id' => $medication->id, 'quantity' => 10]],
        ])->assertSessionHasErrors('allergies');

        $this->assertDatabaseMissing('prescriptions', ['consultation_id' => $consultation->id]);
    }
}

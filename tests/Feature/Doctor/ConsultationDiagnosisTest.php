<?php

namespace Tests\Feature\Doctor;

use App\Enums\ConsultationStatus;
use App\Enums\DiagnosisType;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\DiagnosisCatalog;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationDiagnosisTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_diagnosis_is_unique_and_syncs_to_problem_list(): void
    {
        [$user, $doctor] = $this->doctor();
        $consultation = Consultation::factory()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id]);
        $first = DiagnosisCatalog::factory()->create(['name' => 'First diagnosis']);
        $second = DiagnosisCatalog::factory()->create(['name' => 'Second diagnosis']);

        $this->actingAs($user)->post(route('doctor.consultations.diagnoses.store', $consultation), [
            'diagnosis_catalog_id' => $first->id,
            'diagnosis_type' => DiagnosisType::Primary->value,
            'sync_to_problem_list' => true,
        ])->assertRedirect();

        $this->actingAs($user)->post(route('doctor.consultations.diagnoses.store', $consultation), [
            'diagnosis_catalog_id' => $second->id,
            'diagnosis_type' => DiagnosisType::Primary->value,
            'sync_to_problem_list' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('consultation_diagnoses', ['diagnosis_catalog_id' => $first->id, 'diagnosis_type' => DiagnosisType::Secondary->value]);
        $this->assertDatabaseHas('consultation_diagnoses', ['diagnosis_catalog_id' => $second->id, 'diagnosis_type' => DiagnosisType::Primary->value]);
        $this->assertDatabaseHas('patient_problems', ['patient_id' => $consultation->patient_id, 'diagnosis_catalog_id' => $second->id]);
    }

    public function test_finalized_consultation_blocks_new_diagnosis(): void
    {
        [$user, $doctor] = $this->doctor();
        $consultation = Consultation::factory()->completed()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id]);

        $this->actingAs($user)->post(route('doctor.consultations.diagnoses.store', $consultation), [
            'diagnosis_name' => 'Late diagnosis',
            'diagnosis_type' => DiagnosisType::Primary->value,
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

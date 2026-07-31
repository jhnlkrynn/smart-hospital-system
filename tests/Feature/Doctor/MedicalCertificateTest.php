<?php

namespace Tests\Feature\Doctor;

use App\Enums\MedicalCertificateStatus;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\MedicalCertificate;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_creates_issues_and_voids_certificate(): void
    {
        [$user, $doctor] = $this->doctor();
        $consultation = Consultation::factory()->completed()->create(['doctor_employee_id' => $doctor->id, 'department_id' => $doctor->department_id]);

        $this->actingAs($user)->post(route('doctor.consultations.certificates.store', $consultation), [
            'purpose' => 'School absence',
            'clinical_summary' => 'Patient was assessed.',
            'recommendation' => 'Rest for one day.',
        ])->assertRedirect();

        $certificate = MedicalCertificate::query()->firstOrFail();
        $this->actingAs($user)->post(route('doctor.consultations.certificates.issue', [$consultation, $certificate]))->assertRedirect();
        $this->assertDatabaseHas('medical_certificates', ['id' => $certificate->id, 'status' => MedicalCertificateStatus::Issued->value]);

        $this->actingAs($user)->post(route('doctor.consultations.certificates.void', [$consultation, $certificate]), ['reason' => 'Issued with corrected details.'])
            ->assertRedirect();
        $this->assertDatabaseHas('medical_certificates', ['id' => $certificate->id, 'status' => MedicalCertificateStatus::Void->value]);
    }

    public function test_patient_sees_only_issued_certificates(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        MedicalCertificate::factory()->issued()->create(['patient_id' => $patient->id]);
        MedicalCertificate::factory()->create(['patient_id' => $patient->id, 'purpose' => 'Draft hidden']);

        $this->actingAs($user)->get(route('patient.medical-records.index'))
            ->assertOk()
            ->assertSee('Work absence')
            ->assertDontSee('Draft hidden');
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

<?php

namespace Tests\Feature\Patient;

use App\Enums\ConsultationStatus;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientMedicalRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_sees_only_own_finalized_visible_records_without_internal_notes(): void
    {
        [$user, $patient] = $this->patient();
        $visible = Consultation::factory()->completed()->create(['patient_id' => $patient->id, 'internal_doctor_notes' => 'Private doctor note']);
        $draft = Consultation::factory()->create(['patient_id' => $patient->id, 'status' => ConsultationStatus::InProgress]);
        $other = Consultation::factory()->completed()->create();

        $this->actingAs($user)->get(route('patient.medical-records.index'))
            ->assertOk()
            ->assertSee($visible->department->name)
            ->assertDontSee($draft->consultation_number)
            ->assertDontSee($other->consultation_number)
            ->assertDontSee('Private doctor note');

        $this->actingAs($user)->get(route('patient.medical-records.show', $visible))
            ->assertOk()
            ->assertDontSee('Private doctor note');
    }

    private function patient(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);

        return [$user, $patient];
    }
}

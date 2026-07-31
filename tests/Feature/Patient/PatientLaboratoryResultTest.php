<?php

namespace Tests\Feature\Patient;

use App\Models\LaboratoryResult;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientLaboratoryResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_sees_only_own_released_result_with_disclaimer(): void
    {
        [$user, $patient] = $this->patient();
        $released = LaboratoryResult::factory()->released()->create(['patient_id' => $patient->id, 'technical_notes' => 'Internal calibration note']);
        $draft = LaboratoryResult::factory()->create(['patient_id' => $patient->id]);
        $other = LaboratoryResult::factory()->released()->create();

        $this->actingAs($user)->get(route('patient.laboratory-results.index'))
            ->assertOk()
            ->assertSee($released->laboratoryTest->name)
            ->assertSee('A result outside the reference range')
            ->assertDontSee($draft->laboratoryTest->name)
            ->assertDontSee($other->laboratoryTest->name)
            ->assertDontSee('Internal calibration note');

        $this->actingAs($user)->get(route('patient.laboratory-results.download', $released))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
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

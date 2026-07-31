<?php

namespace Tests\Feature\Patient;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPrescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_sees_only_own_prescriptions(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        $own = Prescription::factory()->create(['patient_id' => $patient->id]);
        $other = Prescription::factory()->create();

        $this->actingAs($user)->get(route('patient.prescriptions.index'))
            ->assertOk()
            ->assertSee($own->prescription_number)
            ->assertDontSee($other->prescription_number);
    }
}

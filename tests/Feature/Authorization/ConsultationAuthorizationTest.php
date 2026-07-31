<?php

namespace Tests\Feature\Authorization;

use App\Models\Consultation;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_clinical_roles_cannot_access_doctor_consultation_routes(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $consultation = Consultation::factory()->create();

        foreach (['pharmacist', 'laboratory-staff', 'cashier'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)->get(route('doctor.consultations.show', $consultation))->assertForbidden();
        }
    }

    public function test_guest_is_redirected_from_medical_records(): void
    {
        $this->get(route('patient.medical-records.index'))->assertRedirect(route('login'));
    }
}

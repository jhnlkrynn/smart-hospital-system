<?php

namespace Tests\Feature\Authorization;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_patient_cannot_access_admin_patient_management(): void
    {
        $this->get(route('admin.patients.index'))->assertRedirect(route('login'));

        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('patient');

        $this->actingAs($user)->get(route('admin.patients.index'))->assertForbidden();
    }

    public function test_cashier_cannot_view_patient_allergies_or_conditions_forms(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        $patient = Patient::factory()->create();

        $this->actingAs($cashier)->get(route('admin.patients.show', $patient))->assertOk()->assertDontSee('Add Allergy')->assertDontSee('Add Condition');
    }
}

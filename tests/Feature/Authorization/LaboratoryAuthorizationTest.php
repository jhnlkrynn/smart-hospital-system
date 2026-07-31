<?php

namespace Tests\Feature\Authorization;

use App\Models\LaboratoryRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_and_pharmacist_cannot_view_laboratory_work_queue(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        LaboratoryRequest::factory()->create();

        foreach (['cashier', 'pharmacist'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)->get(route('laboratory.requests.index'))->assertForbidden();
        }
    }

    public function test_guest_redirected_from_patient_laboratory_results(): void
    {
        $this->get(route('patient.laboratory-results.index'))->assertRedirect(route('login'));
    }
}

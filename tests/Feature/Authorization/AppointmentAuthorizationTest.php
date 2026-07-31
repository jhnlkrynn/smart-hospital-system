<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpermitted_roles_cannot_access_appointment_management(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('pharmacist');
        $patient = User::factory()->create();
        $patient->assignRole('patient');

        $this->actingAs($pharmacist)->get(route('admin.appointments.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.appointments.index'))->assertForbidden();
        auth()->logout();
        $this->get(route('admin.appointments.index'))->assertRedirect('/login');
    }
}

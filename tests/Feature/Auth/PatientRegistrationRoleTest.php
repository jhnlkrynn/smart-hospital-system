<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_assigns_patient_role(): void
    {
        $this->seedAccessControl();

        $this->post('/register', [
            'name' => 'Public Patient',
            'email' => 'public-patient@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('patient.dashboard', absolute: false));

        $user = User::where('email', 'public-patient@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('patient'));
    }

    public function test_public_registration_cannot_assign_administrator_role(): void
    {
        $this->seedAccessControl();

        $this->post('/register', [
            'name' => 'Role Injection',
            'email' => 'role-injection@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'super-admin',
        ]);

        $user = User::where('email', 'role-injection@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('patient'));
        $this->assertFalse($user->hasRole('super-admin'));
    }

    private function seedAccessControl(): void
    {
        $this->seed([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}

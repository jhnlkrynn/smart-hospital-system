<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\AccessControl;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_redirects_to_its_dashboard_after_login(): void
    {
        $this->seedAccessControl();

        foreach (AccessControl::ROLE_DASHBOARDS as $role => $routeName) {
            $user = User::factory()->create([
                'email' => "{$role}@example.test",
                'password' => 'password',
            ]);
            $user->assignRole($role);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route($routeName, absolute: false));

            $this->post('/logout');
        }
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

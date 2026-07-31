<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use App\Support\AccessControl;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_can_access_only_its_own_dashboard(): void
    {
        $this->seedAccessControl();

        foreach (AccessControl::ROLE_DASHBOARDS as $role => $routeName) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)->get(route($routeName))->assertOk();

            foreach (AccessControl::ROLE_DASHBOARDS as $otherRole => $otherRouteName) {
                if ($otherRole === $role) {
                    continue;
                }

                $this->actingAs($user)->get(route($otherRouteName))->assertForbidden();
            }
        }
    }

    public function test_guests_cannot_access_dashboards(): void
    {
        foreach (AccessControl::ROLE_DASHBOARDS as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    public function test_users_without_roles_are_handled_safely(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Account Setup Required');
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

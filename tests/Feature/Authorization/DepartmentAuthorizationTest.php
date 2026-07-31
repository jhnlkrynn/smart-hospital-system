<?php

namespace Tests\Feature\Authorization;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_cannot_access_department_management(): void
    {
        $this->seedAccessControl();
        $user = User::factory()->create();
        $user->assignRole('patient');

        $this->actingAs($user)->get(route('admin.departments.index'))->assertForbidden();
    }

    public function test_unauthorized_staff_cannot_create_departments(): void
    {
        $this->seedAccessControl();
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->actingAs($user)->get(route('admin.departments.create'))->assertForbidden();
    }

    private function seedAccessControl(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
    }
}

<?php

namespace Tests\Feature\Authorization;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_own_employment_profile(): void
    {
        $this->seedAccessControl();
        $department = Department::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('doctor');
        Employee::factory()->create(['user_id' => $user->id, 'department_id' => $department->id]);

        $this->actingAs($user)->get(route('profile.employment'))->assertOk()->assertSee('My Employment Profile');
    }

    public function test_patient_cannot_access_employee_management(): void
    {
        $this->seedAccessControl();
        $user = User::factory()->create();
        $user->assignRole('patient');

        $this->actingAs($user)->get(route('admin.employees.index'))->assertForbidden();
    }

    public function test_hospital_admin_cannot_archive_super_admin_employee(): void
    {
        $this->seedAccessControl();
        $department = Department::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('hospital-admin');
        $super = User::factory()->create();
        $super->assignRole('super-admin');
        $employee = Employee::factory()->create(['user_id' => $super->id, 'department_id' => $department->id]);

        $this->actingAs($admin)->delete(route('admin.employees.destroy', $employee))->assertForbidden();
    }

    private function seedAccessControl(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
    }
}

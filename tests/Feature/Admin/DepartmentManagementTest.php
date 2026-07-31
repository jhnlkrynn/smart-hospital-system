<?php

namespace Tests\Feature\Admin;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_view_and_create_department(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->get(route('admin.departments.index'))->assertOk();

        $this->actingAs($admin)->post(route('admin.departments.store'), [
            'code' => 'icu',
            'name' => 'Intensive Care Unit',
            'description' => 'Critical care.',
            'location' => 'Second Floor',
            'contact_number' => '+63 900 123 4567',
            'email' => 'icu@hospital.test',
            'status' => DepartmentStatus::Active->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('departments', ['code' => 'ICU', 'name' => 'Intensive Care Unit']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'module' => 'departments']);
    }

    public function test_department_code_and_name_must_be_unique(): void
    {
        $admin = $this->adminUser();
        Department::factory()->create(['code' => 'ADM', 'name' => 'Administration']);

        $this->actingAs($admin)->post(route('admin.departments.store'), [
            'code' => 'ADM',
            'name' => 'Administration',
            'status' => DepartmentStatus::Active->value,
        ])->assertSessionHasErrors(['code', 'name']);
    }

    public function test_department_can_be_updated_searched_filtered_and_restored(): void
    {
        $admin = $this->adminUser();
        $department = Department::factory()->create(['code' => 'RAD', 'name' => 'Radiology']);

        $this->actingAs($admin)->put(route('admin.departments.update', $department), [
            'code' => 'IMG',
            'name' => 'Imaging',
            'status' => DepartmentStatus::Inactive->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'code' => 'IMG', 'name' => 'Imaging']);

        $this->actingAs($admin)->get(route('admin.departments.index', ['search' => 'Imaging', 'status' => 'inactive']))
            ->assertOk()
            ->assertSee('Imaging');

        $this->actingAs($admin)->delete(route('admin.departments.destroy', $department->refresh()))->assertRedirect();
        $this->assertSoftDeleted('departments', ['id' => $department->id]);

        $this->actingAs($admin)->patch(route('admin.departments.restore', $department->id))->assertRedirect();
        $this->assertNotSoftDeleted('departments', ['id' => $department->id]);
    }

    public function test_department_with_active_employees_cannot_be_archived(): void
    {
        $admin = $this->adminUser();
        $department = Department::factory()->create();
        Employee::factory()->create(['department_id' => $department->id]);

        $this->actingAs($admin)->delete(route('admin.departments.destroy', $department))
            ->assertSessionHasErrors('department');
    }

    private function adminUser(): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }
}

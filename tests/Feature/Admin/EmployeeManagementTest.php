<?php

namespace Tests\Feature\Admin;

use App\Enums\DepartmentStatus;
use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\Sex;
use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_create_employee_and_user_account(): void
    {
        $admin = $this->adminUser('super-admin');
        $department = Department::factory()->create(['status' => DepartmentStatus::Active]);

        $this->actingAs($admin)->post(route('admin.employees.store'), $this->payload($department, [
            'role' => 'doctor',
            'specialization' => 'Family Medicine',
        ]))->assertRedirect();

        $employee = Employee::where('email', 'new.employee@hospital.test')->firstOrFail();
        $this->assertMatchesRegularExpression('/^EMP-\d{4}-\d{6}$/', $employee->employee_number);
        $this->assertTrue($employee->user->hasRole('doctor'));
        $this->assertTrue(Hash::check('Password123!', $employee->user->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'module' => 'employees']);
    }

    public function test_patient_role_cannot_be_assigned_to_employee(): void
    {
        $admin = $this->adminUser('super-admin');
        $department = Department::factory()->create(['status' => DepartmentStatus::Active]);

        $this->actingAs($admin)->post(route('admin.employees.store'), $this->payload($department, ['role' => 'patient']))
            ->assertSessionHasErrors('role');
    }

    public function test_hospital_admin_cannot_create_super_admin_employee(): void
    {
        $admin = $this->adminUser('hospital-admin');
        $department = Department::factory()->create(['status' => DepartmentStatus::Active]);

        $this->actingAs($admin)->post(route('admin.employees.store'), $this->payload($department, ['role' => 'super-admin']))
            ->assertSessionHasErrors('role');
    }

    public function test_doctor_requires_specialization_and_email_must_be_unique(): void
    {
        $admin = $this->adminUser('super-admin');
        $department = Department::factory()->create(['status' => DepartmentStatus::Active]);
        User::factory()->create(['email' => 'new.employee@hospital.test']);

        $this->actingAs($admin)->post(route('admin.employees.store'), $this->payload($department, [
            'role' => 'doctor',
            'specialization' => null,
        ]))->assertSessionHasErrors(['email', 'specialization']);
    }

    public function test_profile_photo_rejects_executable_files(): void
    {
        $admin = $this->adminUser('super-admin');
        $department = Department::factory()->create(['status' => DepartmentStatus::Active]);

        $this->actingAs($admin)->post(route('admin.employees.store'), $this->payload($department, [
            'profile_photo' => \Illuminate\Http\UploadedFile::fake()->create('payload.exe', 10, 'application/x-msdownload'),
        ]))->assertSessionHasErrors('profile_photo');
    }

    public function test_employee_can_be_updated_archived_and_restored(): void
    {
        $admin = $this->adminUser('super-admin');
        $department = Department::factory()->create(['status' => DepartmentStatus::Active]);
        $employee = Employee::factory()->create(['department_id' => $department->id]);
        $employee->user->assignRole('nurse');

        $this->actingAs($admin)->put(route('admin.employees.update', $employee), $this->payload($department, [
            'email' => $employee->email,
            'role' => 'pharmacist',
            'first_name' => 'Updated',
        ], includePassword: false))->assertRedirect();

        $this->assertTrue($employee->user->fresh()->hasRole('pharmacist'));

        $this->actingAs($admin)->delete(route('admin.employees.destroy', $employee->refresh()))->assertRedirect();
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        $this->assertSame(UserStatus::Inactive, $employee->user->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.employees.restore', $employee->id))->assertRedirect();
        $this->assertNotSoftDeleted('employees', ['id' => $employee->id]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function payload(Department $department, array $overrides = [], bool $includePassword = true): array
    {
        $payload = [
            'email' => 'new.employee@hospital.test',
            'role' => 'nurse',
            'account_status' => UserStatus::Active->value,
            'first_name' => 'Nora',
            'last_name' => 'Dela Cruz',
            'date_of_birth' => '1990-01-01',
            'sex' => Sex::Female->value,
            'department_id' => $department->id,
            'position' => 'Staff Nurse',
            'employment_type' => EmploymentType::Regular->value,
            'employment_status' => EmploymentStatus::Active->value,
            'hire_date' => now()->toDateString(),
        ];

        if ($includePassword) {
            $payload['temporary_password'] = 'Password123!';
            $payload['temporary_password_confirmation'] = 'Password123!';
        }

        return array_merge($payload, $overrides);
    }

    private function adminUser(string $role): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

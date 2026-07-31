<?php

namespace Tests\Feature\Admin;

use App\Enums\DayOfWeek;
use App\Enums\EmploymentStatus;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_doctor_schedule(): void
    {
        [$admin, $doctor] = $this->users();

        $this->actingAs($admin)->post(route('admin.doctor-schedules.store'), [
            'doctor_employee_id' => $doctor->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'slot_duration_minutes' => 30,
            'maximum_appointments' => 8,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('doctor_schedules', ['doctor_employee_id' => $doctor->id, 'day_of_week' => DayOfWeek::Monday->value]);
    }

    public function test_schedule_requires_doctor_role_and_valid_break(): void
    {
        [$admin] = $this->users();
        $nurse = $this->employeeWithRole('nurse');

        $this->actingAs($admin)->post(route('admin.doctor-schedules.store'), [
            'doctor_employee_id' => $nurse->id,
            'day_of_week' => DayOfWeek::Tuesday->value,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'break_start_time' => '07:30',
            'break_end_time' => '08:30',
            'slot_duration_minutes' => 30,
            'maximum_appointments' => 8,
        ])->assertSessionHasErrors(['doctor_employee_id', 'break_start_time']);
    }

    public function test_overlapping_schedule_is_rejected(): void
    {
        [$admin, $doctor] = $this->users();
        DoctorSchedule::factory()->create(['doctor_employee_id' => $doctor->id, 'day_of_week' => DayOfWeek::Wednesday]);

        $this->actingAs($admin)->post(route('admin.doctor-schedules.store'), [
            'doctor_employee_id' => $doctor->id,
            'day_of_week' => DayOfWeek::Wednesday->value,
            'start_time' => '13:00',
            'end_time' => '17:00',
            'slot_duration_minutes' => 30,
            'maximum_appointments' => 8,
        ])->assertSessionHasErrors(['day_of_week']);
    }

    private function users(): array
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('hospital-admin');

        return [$admin, $this->employeeWithRole('doctor')];
    }

    private function employeeWithRole(string $role): Employee
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return Employee::factory()->create([
            'user_id' => $user->id,
            'department_id' => Department::factory(),
            'employment_status' => EmploymentStatus::Active,
        ]);
    }
}

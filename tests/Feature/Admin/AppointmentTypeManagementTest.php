<?php

namespace Tests\Feature\Admin;

use App\Models\AppointmentType;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_create_appointment_type(): void
    {
        $admin = $this->user('hospital-admin');

        $this->actingAs($admin)->post(route('admin.appointment-types.store'), [
            'code' => 'CONSULT',
            'name' => 'General Consultation',
            'default_duration_minutes' => 30,
            'requires_approval' => true,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('appointment_types', ['code' => 'CONSULT', 'name' => 'General Consultation']);
    }

    public function test_code_and_name_must_be_unique(): void
    {
        $admin = $this->user('hospital-admin');
        AppointmentType::factory()->create(['code' => 'CHECKUP', 'name' => 'Routine Checkup']);

        $this->actingAs($admin)->post(route('admin.appointment-types.store'), [
            'code' => 'CHECKUP',
            'name' => 'Routine Checkup',
            'default_duration_minutes' => 30,
        ])->assertSessionHasErrors(['code', 'name']);
    }

    public function test_patient_cannot_manage_appointment_types(): void
    {
        $patient = $this->user('patient');

        $this->actingAs($patient)->get(route('admin.appointment-types.index'))->assertForbidden();
    }

    private function user(string $role): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

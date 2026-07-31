<?php

namespace Tests\Feature\Admin;

use App\Enums\PatientStatus;
use App\Enums\Sex;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_create_patient_with_account(): void
    {
        $admin = $this->userWithRole('super-admin');

        $this->actingAs($admin)->post(route('admin.patients.store'), $this->payload([
            'create_account' => '1',
            'temporary_password' => 'Password123!',
            'temporary_password_confirmation' => 'Password123!',
        ]))->assertRedirect();

        $patient = Patient::where('email', 'new.patient@hospital.test')->firstOrFail();
        $this->assertMatchesRegularExpression('/^PAT-\d{4}-\d{6}$/', $patient->patient_number);
        $this->assertSame(64, strlen($patient->getRawOriginal('qr_token')));
        $this->assertStringNotContainsString($patient->first_name, $patient->getRawOriginal('qr_token'));
        $this->assertTrue($patient->user->hasRole('patient'));
        $this->assertTrue(Hash::check('Password123!', $patient->user->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'created_with_account', 'module' => 'patients']);
    }

    public function test_nurse_can_register_patient_without_account(): void
    {
        $nurse = $this->userWithRole('nurse');

        $this->actingAs($nurse)->post(route('admin.patients.store'), $this->payload([
            'email' => null,
            'contact_number' => '+63 900 333 4444',
        ]))->assertRedirect();

        $this->assertDatabaseHas('patients', ['first_name' => 'Nina', 'user_id' => null]);
    }

    public function test_patient_can_be_updated_archived_and_restored(): void
    {
        $admin = $this->userWithRole('super-admin');
        $patient = Patient::factory()->create();

        $this->actingAs($admin)->put(route('admin.patients.update', $patient), $this->payload([
            'email' => $patient->email,
            'first_name' => 'Updated',
        ], includeAccount: false))->assertRedirect();

        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'first_name' => 'Updated']);

        $this->actingAs($admin)->delete(route('admin.patients.destroy', $patient->refresh()))->assertRedirect();
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);

        $this->actingAs($admin)->patch(route('admin.patients.restore', $patient->id))->assertRedirect();
        $this->assertNotSoftDeleted('patients', ['id' => $patient->id]);
    }

    public function test_duplicate_detection_warns_but_does_not_auto_block(): void
    {
        $admin = $this->userWithRole('super-admin');
        Patient::factory()->create(['first_name' => 'Nina', 'last_name' => 'Santos', 'date_of_birth' => '1994-05-02']);

        $this->actingAs($admin)->post(route('admin.patients.store'), $this->payload())
            ->assertSessionHas('possible_duplicates');
    }

    private function payload(array $overrides = [], bool $includeAccount = true): array
    {
        return array_merge([
            'first_name' => 'Nina',
            'last_name' => 'Santos',
            'date_of_birth' => '1994-05-02',
            'sex' => Sex::Female->value,
            'email' => 'new.patient@hospital.test',
            'contact_number' => '+63 900 123 4567',
            'registration_date' => now()->toDateString(),
            'status' => PatientStatus::Active->value,
        ], $overrides);
    }

    private function userWithRole(string $role): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

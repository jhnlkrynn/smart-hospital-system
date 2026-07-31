<?php

namespace Tests\Feature\Patient;

use App\Models\Patient;
use App\Models\User;
use App\Services\Patient\PatientQrService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientQrLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_lookup_patient_by_qr_token_and_regenerate_invalidates_old_token(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $patient = Patient::factory()->create();
        $old = $patient->getRawOriginal('qr_token');

        $this->actingAs($admin)->get(route('patient-lookup.show', $old))->assertOk()->assertSee($patient->patient_number);

        app(PatientQrService::class)->regenerateToken($patient, $admin);
        $this->actingAs($admin)->get(route('patient-lookup.show', $old))->assertNotFound();
        $this->assertDatabaseHas('audit_logs', ['action' => 'qr_regenerated', 'module' => 'patients']);
    }

    public function test_unauthorized_user_cannot_use_qr_lookup(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $patientUser = User::factory()->create();
        $patientUser->assignRole('patient');

        $this->actingAs($patientUser)->get(route('patient-lookup.index'))->assertForbidden();
    }
}

<?php

namespace Tests\Feature\Patient;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_view_and_update_own_profile_only(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        $other = Patient::factory()->create();

        $this->actingAs($user)->get(route('patient.profile.show'))->assertOk()->assertSee($patient->patient_number);
        $this->actingAs($user)->put(route('patient.profile.update'), [
            'contact_number' => '+63 900 555 1111',
            'status' => 'archived',
            'blood_type' => 'O+',
        ])->assertRedirect();

        $patient->refresh();
        $this->assertSame('+63 900 555 1111', $patient->contact_number);
        $this->assertSame('active', $patient->status->value);
        $this->assertNull($patient->blood_type);
        $this->actingAs($user)->get(route('admin.patients.show', $other))->assertForbidden();
    }
}

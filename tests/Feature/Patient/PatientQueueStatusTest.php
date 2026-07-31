<?php

namespace Tests\Feature\Patient;

use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientQueueStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_sees_only_own_queue_status_page(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        $own = PatientQueue::factory()->create(['patient_id' => $patient->id, 'queue_number' => 'GEN-20260731-001']);
        PatientQueue::factory()->create(['queue_number' => 'GEN-20260731-002']);

        $this->actingAs($user)->get(route('patient.queues.index'))
            ->assertOk()
            ->assertSee($own->queue_number)
            ->assertDontSee('GEN-20260731-002');
    }
}

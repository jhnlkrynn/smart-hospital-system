<?php

namespace Tests\Feature\Nurse;

use App\Enums\QueueStatus;
use App\Enums\TriageAcuity;
use App\Models\PatientQueue;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriageWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_can_record_triage_and_vitals_with_bmi(): void
    {
        $nurse = $this->user('nurse');
        $queue = PatientQueue::factory()->create(['status' => QueueStatus::Called]);

        $this->actingAs($nurse)->post(route('nurse.triage.store', $queue), [
            'chief_complaint' => 'Fictional chest discomfort',
            'pain_scale' => 6,
            'fall_risk_score' => 4,
            'acuity' => TriageAcuity::Urgent->value,
            'allergies_reviewed' => true,
            'blood_pressure_systolic' => 130,
            'blood_pressure_diastolic' => 84,
            'pulse_rate' => 90,
            'respiratory_rate' => 18,
            'temperature_c' => 37.2,
            'oxygen_saturation' => 97,
            'height_cm' => 170,
            'weight_kg' => 70,
        ])->assertRedirect();

        $this->assertDatabaseHas('triage_records', [
            'queue_id' => $queue->id,
            'fall_risk_level' => 'moderate',
            'acuity' => TriageAcuity::Urgent->value,
        ]);
        $this->assertDatabaseHas('vital_signs', ['queue_id' => $queue->id, 'bmi' => 24.22]);
        $this->assertDatabaseHas('queues', ['id' => $queue->id, 'status' => QueueStatus::Triaged->value]);
    }

    private function user(string $role): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}

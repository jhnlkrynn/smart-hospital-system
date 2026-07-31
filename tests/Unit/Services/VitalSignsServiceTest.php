<?php

namespace Tests\Unit\Services;

use App\Models\PatientQueue;
use App\Models\User;
use App\Services\Queue\VitalSignsService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VitalSignsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_bmi_is_calculated_from_height_and_weight(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $nurse = User::factory()->create();
        $nurse->assignRole('nurse');
        $queue = PatientQueue::factory()->create();

        $vital = app(VitalSignsService::class)->record($queue, $nurse, [
            'height_cm' => 180,
            'weight_kg' => 81,
        ]);

        $this->assertEquals('25.00', $vital->bmi);
    }
}

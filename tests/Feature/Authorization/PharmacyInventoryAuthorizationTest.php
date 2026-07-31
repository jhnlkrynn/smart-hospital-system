<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyInventoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_pharmacist_cannot_access_pharmacy_inventory(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->actingAs($user)->get(route('pharmacist.inventory.index'))->assertForbidden();
    }
}

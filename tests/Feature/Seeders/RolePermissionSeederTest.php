<?php

namespace Tests\Feature\Seeders;

use App\Support\AccessControl;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_control_seeders_are_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(count(AccessControl::ROLES), Role::count());
        $this->assertSame(count(array_unique(AccessControl::PERMISSIONS)), Permission::count());
    }

    public function test_expected_permissions_are_assigned_by_least_privilege(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Role::findByName('super-admin')->hasPermissionTo('payments.refund'));
        $this->assertFalse(Role::findByName('hospital-admin')->hasPermissionTo('roles.manage'));
        $this->assertFalse(Role::findByName('doctor')->hasPermissionTo('payments.verify'));
        $this->assertFalse(Role::findByName('cashier')->hasPermissionTo('medical-records.view'));
        $this->assertTrue(Role::findByName('patient')->hasPermissionTo('patients.view-own-record'));
        $this->assertFalse(Role::findByName('patient')->hasPermissionTo('patients.view-medical-records'));
    }
}

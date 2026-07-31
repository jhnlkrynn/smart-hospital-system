<?php

namespace Database\Seeders;

use App\Support\AccessControl;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AccessControl::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions(array_unique($permissions));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

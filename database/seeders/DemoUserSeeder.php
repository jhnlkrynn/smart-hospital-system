<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AccessControl::DEMO_USERS as $demoUser) {
            $user = User::updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'password' => Hash::make(AccessControl::DEMO_PASSWORD),
                    'email_verified_at' => now(),
                    'status' => UserStatus::Active,
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'deactivated_at' => null,
                    'deactivated_by' => null,
                ]
            );

            $user->syncRoles([$demoUser['role']]);
        }
    }
}

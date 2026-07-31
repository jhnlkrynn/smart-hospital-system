<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_and_last_login_fields_are_updated(): void
    {
        $this->seedAccessControl();

        $user = $this->patientUser(['status' => UserStatus::Active]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('patient.dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
        $this->assertSame(0, $user->failed_login_attempts);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->assertBlockedStatus(UserStatus::Inactive);
    }

    public function test_suspended_user_cannot_login(): void
    {
        $this->assertBlockedStatus(UserStatus::Suspended);
    }

    public function test_locked_user_cannot_login_while_lock_is_active(): void
    {
        $this->seedAccessControl();

        $user = $this->patientUser([
            'status' => UserStatus::Locked,
            'locked_until' => now()->addHour(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_failed_login_increments_failed_attempts(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertSame(1, $user->refresh()->failed_login_attempts);
    }

    private function assertBlockedStatus(UserStatus $status): void
    {
        $this->seedAccessControl();

        $user = $this->patientUser(['status' => $status]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function patientUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('patient');

        return $user;
    }

    private function seedAccessControl(): void
    {
        $this->seed([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}

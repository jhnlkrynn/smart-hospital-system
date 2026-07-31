<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;

trait DashboardData
{
    /**
     * @return array<string, mixed>
     */
    protected function dashboardData(User $user, string $roleLabel, string $roleSlug): array
    {
        return [
            'user' => $user,
            'roleLabel' => $roleLabel,
            'roleSlug' => $roleSlug,
            'accountStatus' => $user->status->value,
            'lastLogin' => $user->last_login_at instanceof CarbonInterface
                ? $user->last_login_at->format('M d, Y h:i A')
                : 'Not recorded yet',
            'currentDate' => now()->format('M d, Y'),
            'unreadNotifications' => Schema::hasTable('notifications') ? $user->unreadNotifications()->count() : 0,
            'progressItems' => [
                'Authentication and profile management',
                'Roles and permissions',
                'Role-based dashboards',
                'Demo access accounts',
            ],
        ];
    }
}

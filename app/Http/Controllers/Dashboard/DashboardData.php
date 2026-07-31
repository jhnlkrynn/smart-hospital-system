<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
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
                'Patient management and QR identification',
                'Doctor schedules and appointment booking',
            ],
            'appointmentMetrics' => $this->appointmentMetrics($user, $roleSlug),
            'queueMetrics' => $this->queueMetrics($user, $roleSlug),
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    private function appointmentMetrics(User $user, string $roleSlug): array
    {
        if (! Schema::hasTable('appointments')) {
            return [];
        }

        $today = now('Asia/Manila')->toDateString();
        $query = DB::table('appointments')->whereNull('deleted_at');

        if ($roleSlug === 'doctor' && $user->employee) {
            $query->where('doctor_employee_id', $user->employee->id);
        }

        if ($roleSlug === 'patient' && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        }

        return [
            'appointments_today' => (clone $query)->whereDate('appointment_date', $today)->count(),
            'pending_requests' => (clone $query)->where('status', 'pending')->count(),
            'approved_today' => (clone $query)->whereDate('appointment_date', $today)->where('status', 'approved')->count(),
            'upcoming' => (clone $query)->whereDate('appointment_date', '>=', $today)->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function queueMetrics(User $user, string $roleSlug): array
    {
        if (! Schema::hasTable('queues')) {
            return [];
        }

        $today = now('Asia/Manila')->toDateString();
        $query = DB::table('queues')->whereNull('deleted_at')->whereDate('queue_date', $today);

        if ($roleSlug === 'doctor' && $user->employee) {
            $query->where('doctor_employee_id', $user->employee->id);
        }

        if ($roleSlug === 'patient' && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        }

        return [
            'waiting' => (clone $query)->where('status', 'waiting')->count(),
            'called' => (clone $query)->where('status', 'called')->count(),
            'triaged' => (clone $query)->where('status', 'triaged')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];
    }
}

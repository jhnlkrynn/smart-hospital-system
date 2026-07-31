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
                'Queue management, triage, and vital signs',
                'Consultations, diagnoses, and patient medical records',
            ],
            'appointmentMetrics' => $this->appointmentMetrics($user, $roleSlug),
            'queueMetrics' => $this->queueMetrics($user, $roleSlug),
            'consultationMetrics' => $this->consultationMetrics($user, $roleSlug),
            'laboratoryMetrics' => $this->laboratoryMetrics($user, $roleSlug),
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

    /**
     * @return array<string, int>
     */
    private function consultationMetrics(User $user, string $roleSlug): array
    {
        if (! Schema::hasTable('consultations')) {
            return [];
        }

        $query = DB::table('consultations')->whereNull('deleted_at');

        if ($roleSlug === 'doctor' && $user->employee) {
            $query->where('doctor_employee_id', $user->employee->id);
        }

        if ($roleSlug === 'patient' && $user->patient) {
            $query->where('patient_id', $user->patient->id)->where('is_patient_visible', true);
        }

        return [
            'in_progress' => (clone $query)->whereIn('status', ['in_progress', 'reopened', 'paused'])->count(),
            'completed_today' => (clone $query)->whereDate('completed_at', now('Asia/Manila')->toDateString())->count(),
            'completed_total' => (clone $query)->where('status', 'completed')->count(),
            'certificates_issued' => Schema::hasTable('medical_certificates')
                ? DB::table('medical_certificates')->whereNull('deleted_at')->where('status', 'issued')->when($roleSlug === 'doctor' && $user->employee, fn ($q) => $q->where('doctor_employee_id', $user->employee->id))->when($roleSlug === 'patient' && $user->patient, fn ($q) => $q->where('patient_id', $user->patient->id))->count()
                : 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function laboratoryMetrics(User $user, string $roleSlug): array
    {
        if (! Schema::hasTable('laboratory_requests')) {
            return [];
        }

        $today = now('Asia/Manila')->toDateString();
        $requests = DB::table('laboratory_requests')->whereNull('deleted_at');
        $results = Schema::hasTable('laboratory_results') ? DB::table('laboratory_results')->whereNull('deleted_at') : null;

        if ($roleSlug === 'doctor' && $user->employee) {
            $requests->where('requesting_doctor_employee_id', $user->employee->id);
            $results?->whereIn('laboratory_request_id', DB::table('laboratory_requests')->where('requesting_doctor_employee_id', $user->employee->id)->pluck('id'));
        }

        if ($roleSlug === 'patient' && $user->patient) {
            $requests->where('patient_id', $user->patient->id);
            $results?->where('patient_id', $user->patient->id)->where('is_patient_visible', true);
        }

        return [
            'new_requests_today' => (clone $requests)->whereDate('requested_at', $today)->count(),
            'specimen_pending' => (clone $requests)->whereIn('status', ['specimen_pending', 'recollection_required'])->count(),
            'in_process' => (clone $requests)->whereIn('status', ['in_process', 'partially_completed'])->count(),
            'released_today' => $results ? (clone $results)->whereDate('released_at', $today)->count() : 0,
            'critical_open' => $results ? (clone $results)->where('is_critical', true)->whereNotNull('released_at')->count() : 0,
        ];
    }
}

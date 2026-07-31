<?php

namespace Database\Seeders;

use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use App\Services\Queue\QueueService;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::where('email', 'nurse@hospital.test')->first() ?? User::first();
        $department = Department::where('code', 'GEN')->first() ?? Department::first();

        if (! $actor || ! $department) {
            return;
        }

        $service = app(QueueService::class);
        $appointment = Appointment::query()->with('queue')->whereDoesntHave('queue')->first();

        if ($appointment) {
            try {
                $service->checkInAppointment($appointment, $actor, ['is_senior_citizen' => true]);
            } catch (\Throwable) {
                // Keep seeders idempotent when existing data already covers the demo flow.
            }
        }

        $patient = Patient::query()->active()->whereDoesntHave('queues', fn ($query) => $query->whereDate('queue_date', now('Asia/Manila')->toDateString()))->first();

        if ($patient) {
            $service->createWalkIn($patient, $department, $actor, ['is_emergency' => false, 'notes' => 'Fictional walk-in queue seed.']);
        }

        PatientQueue::query()->whereNull('checked_in_at')->update(['checked_in_at' => now()]);
        PatientQueue::query()->where('status', QueueStatus::Waiting->value)->limit(1)->update(['status' => QueueStatus::Called->value, 'called_at' => now()]);
    }
}

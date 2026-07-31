<?php

namespace App\Services\Queue;

use App\Enums\AppointmentStatus;
use App\Enums\QueuePriority;
use App\Enums\QueueStatus;
use App\Enums\VisitType;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\QueueStatusHistory;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QueueService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly AuditLogService $audit,
    ) {}

    public function checkInAppointment(Appointment $appointment, User $actor, array $flags = []): PatientQueue
    {
        if ($appointment->queue()->exists()) {
            throw ValidationException::withMessages(['appointment' => 'This appointment is already checked in.']);
        }

        if ($appointment->status->isTerminal()) {
            throw ValidationException::withMessages(['appointment' => 'Terminal appointments cannot be checked in.']);
        }

        return DB::transaction(function () use ($appointment, $actor, $flags): PatientQueue {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            $priority = $this->priorityFromFlags($flags);
            $queue = $this->createQueue(
                $appointment->patient,
                $appointment->department,
                $actor,
                $priority,
                VisitType::Appointment,
                $flags + [
                    'appointment_id' => $appointment->id,
                    'doctor_employee_id' => $appointment->doctor_employee_id,
                ],
            );
            $appointment->update(['status' => AppointmentStatus::CheckedIn, 'updated_by' => $actor->id]);
            $this->audit->record($actor, 'queue.appointment_checked_in', 'queues', $queue, 'Appointment checked in to queue.');

            return $queue;
        });
    }

    public function createWalkIn(Patient $patient, Department $department, User $actor, array $data): PatientQueue
    {
        return DB::transaction(function () use ($patient, $department, $actor, $data): PatientQueue {
            $queue = $this->createQueue($patient, $department, $actor, $this->priorityFromFlags($data), VisitType::WalkIn, $data);
            $this->audit->record($actor, 'queue.walk_in_created', 'queues', $queue, 'Walk-in patient added to queue.');

            return $queue;
        });
    }

    public function callNext(Department $department, User $actor): PatientQueue
    {
        return DB::transaction(function () use ($department, $actor): PatientQueue {
            $queue = PatientQueue::query()
                ->where('department_id', $department->id)
                ->today()
                ->whereIn('status', [QueueStatus::Waiting->value, QueueStatus::Skipped->value])
                ->lockForUpdate()
                ->waitingOrder()
                ->first();

            if (! $queue) {
                throw ValidationException::withMessages(['queue' => 'No waiting patients are available for this department.']);
            }

            return $this->transition($queue, QueueStatus::Called, $actor, ['called_at' => now(), 'current_location' => 'Calling area']);
        });
    }

    public function transition(PatientQueue $queue, QueueStatus $status, User $actor, array $values = [], ?string $notes = null): PatientQueue
    {
        if ($queue->status->isTerminal()) {
            throw ValidationException::withMessages(['status' => 'Terminal queue records cannot be changed.']);
        }

        return DB::transaction(function () use ($queue, $status, $actor, $values, $notes): PatientQueue {
            $queue = PatientQueue::query()->lockForUpdate()->findOrFail($queue->id);
            $old = $queue->status;
            $timestampValues = match ($status) {
                QueueStatus::InTriage => ['triage_started_at' => now(), 'current_location' => 'Triage'],
                QueueStatus::Triaged => ['triage_completed_at' => now(), 'current_location' => 'Waiting for doctor'],
                QueueStatus::WithDoctor => ['doctor_started_at' => now(), 'current_location' => 'Doctor room'],
                QueueStatus::Completed => ['completed_at' => now(), 'current_location' => 'Completed'],
                QueueStatus::Cancelled => ['cancelled_at' => now(), 'current_location' => 'Cancelled'],
                QueueStatus::NoShow => ['no_show_at' => now(), 'current_location' => 'No-show'],
                default => [],
            };
            $queue->update($values + $timestampValues + ['status' => $status, 'updated_by' => $actor->id]);
            $this->history($queue, $old, $status, $actor, $notes);
            $this->audit->record($actor, 'queue.status_changed', 'queues', $queue, 'Queue status changed.', ['status' => $old->value], ['status' => $status->value]);

            return $queue->refresh();
        });
    }

    private function createQueue(Patient $patient, Department $department, User $actor, QueuePriority $priority, VisitType $visitType, array $data): PatientQueue
    {
        $queue = PatientQueue::create([
            'queue_number' => $this->references->queueNumber($department->code),
            'appointment_id' => $data['appointment_id'] ?? null,
            'patient_id' => $patient->id,
            'doctor_employee_id' => $data['doctor_employee_id'] ?? null,
            'department_id' => $department->id,
            'queue_date' => now('Asia/Manila')->toDateString(),
            'status' => QueueStatus::Waiting,
            'priority' => $priority,
            'visit_type' => $visitType,
            'is_emergency' => (bool) ($data['is_emergency'] ?? false),
            'is_senior_citizen' => (bool) ($data['is_senior_citizen'] ?? false),
            'is_pwd' => (bool) ($data['is_pwd'] ?? false),
            'is_pregnant' => (bool) ($data['is_pregnant'] ?? false),
            'checked_in_at' => now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $this->history($queue, null, QueueStatus::Waiting, $actor, 'Queue created.');

        return $queue;
    }

    private function priorityFromFlags(array $flags): QueuePriority
    {
        if ($flags['is_emergency'] ?? false) {
            return QueuePriority::Emergency;
        }
        if ($flags['is_pregnant'] ?? false) {
            return QueuePriority::Pregnant;
        }
        if ($flags['is_pwd'] ?? false) {
            return QueuePriority::Pwd;
        }
        if ($flags['is_senior_citizen'] ?? false) {
            return QueuePriority::SeniorCitizen;
        }

        return QueuePriority::Routine;
    }

    private function history(PatientQueue $queue, ?QueueStatus $old, QueueStatus $new, User $actor, ?string $notes = null): void
    {
        QueueStatusHistory::create([
            'queue_id' => $queue->id,
            'old_status' => $old?->value,
            'new_status' => $new->value,
            'changed_by' => $actor->id,
            'notes' => $notes,
        ]);
    }
}

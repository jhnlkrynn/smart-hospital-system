<?php

namespace App\Services\Appointment;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\AppointmentType;
use App\Models\Employee;
use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\ReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly DoctorAvailabilityService $availability,
        private readonly AuditLogService $audit,
    ) {}

    public function create(array $data, User $actor, AppointmentSource $source): Appointment
    {
        return DB::transaction(function () use ($data, $actor, $source): Appointment {
            $doctor = Employee::query()->lockForUpdate()->with('user.roles')->findOrFail($data['doctor_employee_id']);
            $patient = Patient::query()->lockForUpdate()->findOrFail($data['patient_id']);
            $type = AppointmentType::query()->where('is_active', true)->findOrFail($data['appointment_type_id']);
            $duration = (int) ($data['duration_minutes'] ?? $type->default_duration_minutes);
            $endTime = $this->availability->assertSlotAvailable($doctor, $patient, $data['appointment_date'], $data['start_time'], $duration);
            $status = $type->requires_approval && $source === AppointmentSource::PatientPortal
                ? AppointmentStatus::Pending
                : AppointmentStatus::Confirmed;

            $appointment = Appointment::create([
                'appointment_number' => $this->references->appointmentNumber(),
                'patient_id' => $patient->id,
                'doctor_employee_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'appointment_type_id' => $type->id,
                'appointment_date' => $data['appointment_date'],
                'start_time' => substr($data['start_time'], 0, 5),
                'end_time' => $endTime,
                'duration_minutes' => $duration,
                'status' => $status,
                'source' => $source,
                'reason_for_visit' => $data['reason_for_visit'],
                'patient_notes' => $data['patient_notes'] ?? null,
                'staff_notes' => $data['staff_notes'] ?? null,
                'confirmed_by' => $status === AppointmentStatus::Confirmed ? $actor->id : null,
                'confirmed_at' => $status === AppointmentStatus::Confirmed ? now() : null,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->history($appointment, null, $status, $actor);
            $this->audit->record($actor, 'appointment.created', 'appointments', $appointment, 'Appointment created.', null, [
                'appointment_number' => $appointment->appointment_number,
                'status' => $status->value,
            ]);

            return $appointment;
        });
    }

    public function approve(Appointment $appointment, User $actor): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::Approved, $actor, [
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ], 'appointment.approved');
    }

    public function reject(Appointment $appointment, User $actor, string $reason): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::Rejected, $actor, [
            'rejection_reason' => $reason,
        ], 'appointment.rejected', $reason);
    }

    public function cancel(Appointment $appointment, User $actor, string $reason): Appointment
    {
        if (! $appointment->status->canBeCancelled()) {
            throw ValidationException::withMessages(['appointment' => 'This appointment can no longer be cancelled.']);
        }

        return $this->transition($appointment, AppointmentStatus::Cancelled, $actor, [
            'cancellation_reason' => $reason,
            'cancelled_by' => $actor->id,
            'cancelled_at' => now(),
        ], 'appointment.cancelled', $reason);
    }

    public function complete(Appointment $appointment, User $actor): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::Completed, $actor, [
            'completed_by' => $actor->id,
            'completed_at' => now(),
        ], 'appointment.completed');
    }

    public function markNoShow(Appointment $appointment, User $actor): Appointment
    {
        return $this->transition($appointment, AppointmentStatus::NoShow, $actor, [], 'appointment.no_show');
    }

    public function reschedule(Appointment $appointment, array $data, User $actor, AppointmentSource $source): Appointment
    {
        if (! $appointment->status->canBeRescheduled()) {
            throw ValidationException::withMessages(['appointment' => 'This appointment can no longer be rescheduled.']);
        }

        return DB::transaction(function () use ($appointment, $data, $actor, $source): Appointment {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            $new = $this->create([
                'patient_id' => $appointment->patient_id,
                'doctor_employee_id' => $data['doctor_employee_id'] ?? $appointment->doctor_employee_id,
                'appointment_type_id' => $data['appointment_type_id'] ?? $appointment->appointment_type_id,
                'appointment_date' => $data['appointment_date'],
                'start_time' => $data['start_time'],
                'duration_minutes' => $data['duration_minutes'] ?? $appointment->duration_minutes,
                'reason_for_visit' => $appointment->reason_for_visit,
                'patient_notes' => $data['patient_notes'] ?? $appointment->patient_notes,
                'staff_notes' => $data['staff_notes'] ?? $appointment->staff_notes,
            ], $actor, $source);

            $new->update(['parent_appointment_id' => $appointment->id]);
            $this->transition($appointment, AppointmentStatus::Rescheduled, $actor, [], 'appointment.rescheduled');

            return $new;
        });
    }

    private function transition(Appointment $appointment, AppointmentStatus $status, User $actor, array $values, string $action, ?string $reason = null): Appointment
    {
        if ($appointment->status->isTerminal()) {
            throw ValidationException::withMessages(['status' => 'Terminal appointments cannot be changed.']);
        }

        return DB::transaction(function () use ($appointment, $status, $actor, $values, $action, $reason): Appointment {
            $old = $appointment->status;
            $appointment->update($values + ['status' => $status, 'updated_by' => $actor->id]);
            $this->history($appointment, $old, $status, $actor, $reason);
            $this->audit->record($actor, $action, 'appointments', $appointment, $status->label().' appointment transition.', ['status' => $old->value], ['status' => $status->value]);

            return $appointment->refresh();
        });
    }

    private function history(Appointment $appointment, ?AppointmentStatus $old, AppointmentStatus $new, User $actor, ?string $reason = null): void
    {
        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'old_status' => $old?->value,
            'new_status' => $new->value,
            'changed_by' => $actor->id,
            'reason' => $reason,
        ]);
    }
}

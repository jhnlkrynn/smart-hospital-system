<?php

namespace App\Services\Appointment;

use App\Enums\ScheduleExceptionType;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleException;
use App\Models\Employee;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DoctorScheduleService
{
    public function __construct(private readonly AuditLogService $audit, private readonly DoctorAvailabilityService $availability) {}

    public function createSchedule(array $data, User $actor): DoctorSchedule
    {
        $this->assertNoScheduleOverlap($data);

        return DB::transaction(function () use ($data, $actor): DoctorSchedule {
            $schedule = DoctorSchedule::create($data + ['created_by' => $actor->id, 'updated_by' => $actor->id]);
            $this->audit->record($actor, 'doctor_schedule.created', 'doctor-schedules', $schedule, 'Doctor schedule created.');

            return $schedule;
        });
    }

    public function updateSchedule(DoctorSchedule $schedule, array $data, User $actor): DoctorSchedule
    {
        $this->assertNoScheduleOverlap($data + ['doctor_employee_id' => $schedule->doctor_employee_id], $schedule->id);

        return DB::transaction(function () use ($schedule, $data, $actor): DoctorSchedule {
            $old = $schedule->only(array_keys($data));
            $schedule->update($data + ['updated_by' => $actor->id]);
            $this->audit->record($actor, 'doctor_schedule.updated', 'doctor-schedules', $schedule, 'Doctor schedule updated.', $old, $schedule->only(array_keys($data)));

            return $schedule;
        });
    }

    public function archiveSchedule(DoctorSchedule $schedule, User $actor): void
    {
        DB::transaction(function () use ($schedule, $actor): void {
            $schedule->delete();
            $this->audit->record($actor, 'doctor_schedule.archived', 'doctor-schedules', $schedule, 'Doctor schedule archived.');
        });
    }

    public function createException(array $data, User $actor): DoctorScheduleException
    {
        return DB::transaction(function () use ($data, $actor): DoctorScheduleException {
            $exception = DoctorScheduleException::create($data + ['created_by' => $actor->id, 'updated_by' => $actor->id]);
            $doctor = Employee::findOrFail($exception->doctor_employee_id);
            $affected = $this->availability->affectedAppointments($doctor, $exception->exception_date->toDateString())->count();
            $action = $exception->exception_type === ScheduleExceptionType::Leave ? 'doctor_schedule.leave_created' : 'doctor_schedule_exception.created';
            $this->audit->record($actor, $action, 'doctor-schedules', $exception, "Schedule exception created. Affected active appointments: {$affected}.");

            return $exception;
        });
    }

    private function assertNoScheduleOverlap(array $data, ?int $ignoreId = null): void
    {
        $exists = DoctorSchedule::query()
            ->active()
            ->where('doctor_employee_id', $data['doctor_employee_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where(function ($query) use ($data): void {
                $from = $data['effective_from'] ?? null;
                $until = $data['effective_until'] ?? null;
                $query->whereNull('effective_until')->orWhereNull('effective_from')
                    ->orWhere(function ($query) use ($from, $until): void {
                        $query->whereDate('effective_from', '<=', $until ?? '9999-12-31')
                            ->whereDate('effective_until', '>=', $from ?? '1900-01-01');
                    });
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['day_of_week' => 'An active schedule already exists for this doctor and day.']);
        }
    }
}

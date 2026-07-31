<?php

namespace App\Services\Appointment;

use App\Enums\AppointmentStatus;
use App\Enums\DayOfWeek;
use App\Enums\EmploymentStatus;
use App\Enums\ScheduleExceptionType;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleException;
use App\Models\Employee;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DoctorAvailabilityService
{
    public const TIMEZONE = 'Asia/Manila';

    /**
     * @return Collection<int, array{start: string, end: string, label: string}>
     */
    public function availableSlots(Employee $doctor, string $date, ?int $patientId = null): Collection
    {
        $day = CarbonImmutable::parse($date, self::TIMEZONE)->startOfDay();

        if ($day->isPast() && ! $day->isToday()) {
            return collect();
        }

        if ($doctor->employment_status !== EmploymentStatus::Active || ! $doctor->user?->hasRole('doctor')) {
            return collect();
        }

        $schedule = $this->scheduleFor($doctor, $day);

        if (! $schedule) {
            return collect();
        }

        $exception = DoctorScheduleException::query()
            ->forDoctor($doctor->id)
            ->forDate($day->toDateString())
            ->latest('id')
            ->first();

        if ($exception && ! $exception->is_available) {
            return collect();
        }

        $start = $this->atTime($day, $exception?->start_time ?: $schedule->start_time);
        $end = $this->atTime($day, $exception?->end_time ?: $schedule->end_time);
        $duration = $schedule->slot_duration_minutes;
        $max = $exception?->maximum_appointments ?: $schedule->maximum_appointments;

        $occupied = Appointment::query()
            ->active()
            ->forDoctor($doctor->id)
            ->forDate($day->toDateString())
            ->get(['start_time', 'end_time']);

        $patientOccupied = $patientId ? Appointment::query()
            ->active()
            ->forPatient($patientId)
            ->forDate($day->toDateString())
            ->get(['start_time', 'end_time']) : collect();

        if ($occupied->count() >= $max) {
            return collect();
        }

        $slots = collect();
        for ($cursor = $start; $cursor->addMinutes($duration)->lte($end); $cursor = $cursor->addMinutes($duration)) {
            $slotEnd = $cursor->addMinutes($duration);

            if ($this->overlapsBreak($cursor, $slotEnd, $day, $schedule)) {
                continue;
            }

            if ($this->overlapsCollection($cursor, $slotEnd, $occupied) || $this->overlapsCollection($cursor, $slotEnd, $patientOccupied)) {
                continue;
            }

            $slots->push([
                'start' => $cursor->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'label' => $cursor->format('h:i A').' - '.$slotEnd->format('h:i A'),
            ]);
        }

        return $slots;
    }

    public function assertSlotAvailable(Employee $doctor, Patient $patient, string $date, string $startTime, int $duration): string
    {
        $day = CarbonImmutable::parse($date, self::TIMEZONE)->startOfDay();
        $start = $this->atTime($day, $startTime);
        $end = $start->addMinutes($duration);

        $slots = $this->availableSlots($doctor, $day->toDateString(), $patient->id);
        $matching = $slots->contains(fn (array $slot): bool => $slot['start'] === $start->format('H:i') && $slot['end'] === $end->format('H:i'));

        if (! $matching) {
            throw ValidationException::withMessages([
                'start_time' => 'That appointment slot is no longer available. Please choose another available time.',
            ]);
        }

        return $end->format('H:i');
    }

    public function affectedAppointments(Employee $doctor, string $date): Collection
    {
        return Appointment::query()
            ->active()
            ->forDoctor($doctor->id)
            ->forDate($date)
            ->with(['patient', 'appointmentType'])
            ->orderBy('start_time')
            ->get();
    }

    private function scheduleFor(Employee $doctor, CarbonImmutable $day): ?DoctorSchedule
    {
        return DoctorSchedule::query()
            ->active()
            ->forDoctor($doctor->id)
            ->forDay(DayOfWeek::fromCarbonIso($day->dayOfWeekIso))
            ->effectiveOn($day->toDateString())
            ->orderByDesc('effective_from')
            ->first();
    }

    private function overlapsBreak(CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $day, DoctorSchedule $schedule): bool
    {
        if (! $schedule->break_start_time || ! $schedule->break_end_time) {
            return false;
        }

        return $start->lt($this->atTime($day, $schedule->break_end_time)) && $end->gt($this->atTime($day, $schedule->break_start_time));
    }

    private function overlapsCollection(CarbonImmutable $start, CarbonImmutable $end, Collection $appointments): bool
    {
        return $appointments->contains(fn ($appointment): bool => $start->lt($this->atTime($start, $appointment->end_time)) && $end->gt($this->atTime($start, $appointment->start_time)));
    }

    private function atTime(CarbonImmutable $date, mixed $time): CarbonImmutable
    {
        return CarbonImmutable::parse($date->toDateString().' '.substr((string) $time, 0, 5), self::TIMEZONE);
    }
}

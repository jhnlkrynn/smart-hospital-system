# Appointment Availability Rules

Availability is centralized in `App\Services\Appointment\DoctorAvailabilityService`.

## Slot Generation

Slots are generated from:

- Active doctor employee status.
- Doctor role assignment.
- Matching active weekly schedule.
- Effective date range.
- Slot duration.
- Optional break period.
- Daily maximum appointments.
- Schedule exceptions.
- Existing active appointments.
- Patient appointment conflicts.

Hospital-facing dates and times use `Asia/Manila`.

## Blocking Rules

A slot is unavailable when:

- The date is in the past.
- The employee is inactive or is not a doctor.
- No weekly schedule matches the date.
- The selected time falls outside schedule hours.
- The selected time overlaps a break.
- A full-day unavailable exception exists.
- The doctor has an overlapping active appointment.
- The patient has an overlapping active appointment.
- The daily maximum has already been reached.

Cancelled, rejected, rescheduled, and no-show appointments do not block slots.

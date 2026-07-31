# Doctor Schedule Management

Doctor schedules define recurring professional availability for active doctor employees.

## Weekly Schedules

`doctor_schedules` stores:

- Doctor employee.
- Day of week.
- Start and end time.
- Slot duration.
- Optional break period.
- Daily appointment maximum.
- Optional clinic room.
- Optional effective date range.
- Active flag.

The system uses named day values from `App\Enums\DayOfWeek` and maps them to Carbon ISO weekdays when calculating availability.

## Exceptions and Leave

`doctor_schedule_exceptions` stores date-specific overrides:

- `leave`
- `unavailable`
- `custom_hours`
- `holiday`
- `emergency`

Unavailable exceptions block all slots for the date. Custom-hours exceptions may provide date-specific start/end time and appointment maximum. Existing appointments are not silently cancelled when an exception is created; affected appointments are counted and audit logged for staff follow-up.

## Doctor Own Schedule

Doctors can view their own schedule and assigned appointments. Own-schedule mutation is permission-ready but remains constrained to the authenticated doctor's employee record.

# Appointment Concurrency

Phase 6 rechecks appointment availability inside `AppointmentService::create()` within a database transaction.

## Protection Strategy

- The doctor employee row is locked while booking.
- The patient row is locked while booking.
- The appointment type is reloaded from the database and must be active.
- The selected slot is recalculated on the server.
- Appointment number generation uses the locked `reference_sequences` table.
- The appointment number column has a unique constraint.

If the slot becomes unavailable between rendering and submission, the booking fails with:

```text
That appointment slot is no longer available. Please choose another available time.
```

SQLite tests cover the slot-recheck behavior, but true simultaneous write contention should be load-tested on the production MySQL or MariaDB configuration.

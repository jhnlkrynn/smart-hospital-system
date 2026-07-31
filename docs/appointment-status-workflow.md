# Appointment Status Workflow

The canonical enum is `App\Enums\AppointmentStatus`.

## Statuses

- `pending`
- `confirmed`
- `approved`
- `rejected`
- `cancelled`
- `rescheduled`
- `checked_in`
- `in_progress`
- `completed`
- `no_show`

Phase 6 implements `pending`, `confirmed`, `approved`, `rejected`, `cancelled`, `rescheduled`, `completed`, and `no_show`.

`checked_in` and `in_progress` are defined for future queue and consultation phases but are not activated through Phase 6 workflows.

## Transitions

Patient portal bookings begin as `pending` when the appointment type requires approval. Staff and admin bookings begin as `confirmed`.

Eligible appointments may move to:

- `approved`
- `rejected`
- `cancelled`
- `rescheduled`
- `completed`
- `no_show`

Terminal statuses cannot be changed through normal routes. Cancellation and rejection require reasons. Every transition writes `appointment_status_histories` and an audit log.

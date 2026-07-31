# Queue Status Workflow

The canonical enum is `App\Enums\QueueStatus`.

## Workflow

```text
waiting -> called -> in_triage -> triaged -> with_doctor -> completed
```

Alternative transitions:

- `waiting` or `called` may become `skipped`.
- Active queue records may become `cancelled`.
- Active queue records may become `no_show`.

Every transition writes:

- `queue_status_histories`
- audit log entry
- actor
- old status
- new status
- optional notes

## Check-in Rules

An appointment can be checked in only once. Terminal appointments cannot be checked in. Check-in updates the appointment status to `checked_in` and creates the queue record.

Walk-in visits create queue records without creating appointment, consultation, billing, or queue-number side effects outside Phase 7.

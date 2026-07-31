# Queue Management

Phase 7 connects appointments and walk-in visits to a live operational queue.

## Implemented Scope

- Appointment check-in from an approved/confirmed appointment.
- Walk-in queue creation for existing active patients.
- Automatic queue numbers by department and date.
- Priority flags for emergency, pregnant, PWD, and senior citizen patients.
- Queue status workflow.
- Calling the next patient by department.
- Doctor queue view for triaged patients assigned to the doctor.
- Patient self-service queue status page.
- Queue status history.
- Waiting-time metrics.
- Audit logging for queue creation and status transitions.

## Queue Numbers

Queue numbers use the existing `ReferenceNumberService`:

```text
DEPT-YYYYMMDD-001
```

Examples:

```text
GEN-20260731-001
ER-20260731-001
```

The department code is used as the prefix and the Asia/Manila date is used as the period.

## Priority Order

The queue is ordered by:

1. Emergency
2. Pregnant
3. PWD
4. Senior Citizen
5. Routine

Within the same priority, earlier check-in time is called first.

## Status Workflow

Implemented statuses:

- Waiting
- Called
- In triage
- Triaged
- With doctor
- Completed
- Skipped
- Cancelled
- No-show

Terminal queue records cannot be changed through normal service transitions.

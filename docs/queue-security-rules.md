# Queue Security Rules

Phase 7 follows least-privilege role access.

## Access

- Super Admin: full queue, triage, and vital-sign access.
- Hospital Admin: queue oversight and operational management.
- Nurse: queue management, calling, triage, and vital-sign recording.
- Doctor: own doctor queue and completing assigned queue records.
- Patient: own queue status only.
- Pharmacist, Laboratory Staff, and Cashier: no queue-management access by default.

## Privacy

Patient queue screens show only operational data needed by the role. Patient-facing queue status does not show triage notes or vital signs.

## Server Authority

Queue numbers, priority calculation, status transitions, BMI, queue timestamps, and actor fields are generated server-side. Browser input cannot assign queue numbers or force protected statuses.

## Audit

Queue creation, appointment check-in, walk-in creation, status changes, triage completion, and vital-sign recording are audited.

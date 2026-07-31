# Reference Number Generation

Phase 4 introduces `App\Services\ReferenceNumberService`. Phase 5 reuses the same service for patient numbers.

## Employee Numbers

Format:

```text
EMP-YYYY-000001
```

The year uses the Asia/Manila timezone.

## Patient Numbers

Format:

```text
PAT-YYYY-000001
```

The patient number is created when a patient profile is registered. It is separate from the QR lookup token and may be safely displayed on patient-facing cards and staff identity views.

## Appointment Numbers

Format:

```text
APT-YYYY-000001
```

The appointment number is generated when the booking transaction creates the appointment. It is never accepted from patient or staff form input.

## Queue Numbers

Format:

```text
DEPT-YYYYMMDD-001
```

The department code is the prefix, and the date uses the Asia/Manila timezone. Queue numbers are generated when an appointment is checked in or a walk-in patient is added to the queue.

## Collision Prevention

The service uses the `reference_sequences` table:

- `key`
- `last_number`

Generation happens inside a database transaction. The sequence row is locked with `lockForUpdate()`, incremented, and formatted. The target table also has a unique constraint, so a database collision cannot silently create duplicates.

Do not use table row counts as the only reference source.

Future system settings may make prefixes configurable.

# Reference Number Generation

Phase 4 introduces `App\Services\ReferenceNumberService`.

## Employee Numbers

Format:

```text
EMP-YYYY-000001
```

The year uses the Asia/Manila timezone.

## Collision Prevention

The service uses the `reference_sequences` table:

- `key`
- `last_number`

Generation happens inside a database transaction. The sequence row is locked with `lockForUpdate()`, incremented, and formatted. The target table also has a unique constraint, so a database collision cannot silently create duplicates.

Do not use table row counts as the only reference source.

Future system settings may make prefixes configurable.

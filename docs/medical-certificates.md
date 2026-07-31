# Medical Certificates

Doctors can create medical certificate drafts from a consultation. Certificates use `MEDCERT-{year}-{sequence}` reference numbers.

## Statuses

- `draft`: editable by the issuing doctor.
- `issued`: visible to the patient and locked from editing.
- `void`: retained for audit history and no longer valid.

Voiding requires a reason. Issued certificates appear in the patient medical-record portal.

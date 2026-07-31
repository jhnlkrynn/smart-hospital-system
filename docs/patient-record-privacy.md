# Patient Record Privacy

Phase 8 separates doctor-facing clinical notes from patient-facing medical records.

## Patient Portal Filters

Patient medical-record routes require:

- authenticated patient role,
- consultation ownership,
- `completed` consultation status,
- `is_patient_visible = true`.

## Hidden From Patients

The portal does not display internal doctor notes, draft records, confidential attachments, void certificates, or records belonging to another patient.

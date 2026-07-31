# Patient Document Security

Patient documents are private administrative attachments for Phase 5. They are not a medical-record module.

## Storage

Documents are stored on the local private disk under:

```text
patient-documents/{patient_id}
```

Only metadata is stored in the database, including title, original filename, stored path, MIME type, size, document type, uploaded user, and uploaded timestamp.

## Validation

Uploads are limited to PDF and common image formats. Executable or script-like uploads are rejected by validation. File size is capped by the `StorePatientDocumentRequest`.

## Authorization

Uploading requires `patients.manage-documents`. Downloading requires `patients.download-documents`. Download access is checked against the parent patient record and the requested document must belong to that patient.

## Audit

Uploads and downloads write audit logs with patient/document identifiers and actor context. File contents are not copied into audit logs.

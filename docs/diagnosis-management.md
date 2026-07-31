# Diagnosis Management

Diagnoses are stored as consultation-specific snapshots so historical records remain accurate even if the diagnosis catalog later changes.

## Catalog

The `diagnosis_catalog` table stores reusable diagnosis codes, names, categories, active flags, and patient visibility defaults. Authorized administrators manage catalog records at `/admin/diagnosis-catalog`.

## Consultation Diagnoses

Doctors can add catalog-based or custom diagnoses to active consultations. Supported types are primary, secondary, provisional, differential, and historical. Supported statuses are active, resolved, ruled out, chronic, and inactive.

Only one primary diagnosis is allowed per consultation. Adding a new primary diagnosis automatically changes the previous primary diagnosis to secondary.

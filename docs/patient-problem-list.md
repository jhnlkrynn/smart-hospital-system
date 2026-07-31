# Patient Problem List

The patient problem list is populated from diagnoses that are marked for sync. Primary and secondary diagnoses sync by default.

## Duplicate Control

The sync service avoids duplicate active problems for the same patient and diagnosis catalog item. For custom diagnoses, it checks the active problem name.

## Visibility

Problem-list records use `is_patient_visible` so future patient-facing EMR screens can filter sensitive clinical items without deleting the clinical record.

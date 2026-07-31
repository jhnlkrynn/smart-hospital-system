# Laboratory Result Workflow

Results support numeric, text, qualitative, boolean, structured, and attachment-only result types. Numeric results can resolve a reference range and compute an abnormal flag.

Verification requires `laboratory-results.verify`. Release requires `laboratory-results.release`. Patients see only released results where `is_patient_visible` is true. Released results are not edited directly; amendments create version snapshots and require a reason.

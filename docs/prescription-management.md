# Prescription Management

Doctors create prescriptions from assigned consultations only. Patient, doctor, department, appointment, and prescription number are derived server-side.

Supported Phase 10 statuses are draft, finalized, reviewed, partially reserved, reserved, cancelled, replaced, and expired. Dispensed statuses are intentionally excluded because dispensing belongs to Phase 11.

Before finalization, a prescription must have at least one active item and no unacknowledged blocking allergy warnings.

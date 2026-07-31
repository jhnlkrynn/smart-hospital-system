# Database Design Plan

Use `id` primary keys unless a future integration justifies UUIDs. Patient QR codes must store secure random lookup tokens, not raw patient data. Monetary values use `decimal(12,2)` or more precision where needed. Dates without times use `date`; workflow moments use nullable timestamps. Do not store calculated age.

Phase 10 adds medication catalog, prescription, allergy warning, pharmacy purchase, stock batch, reservation, transfer, adjustment, stock count, and inventory ledger tables. Inventory is batch-first and keeps reservations separate from on-hand quantity so Phase 11 dispensing can consume reserved stock cleanly.

Generated reference numbers must be unique and indexed. They should be produced through a reusable `ReferenceNumberService` using a transaction, a per-prefix/year or per-prefix/date sequence table or row lock, and a final unique database constraint retry. Do not use table row counts as the only sequence source.

## Table Plan

| Table | Purpose | Main columns | Keys and indexes | Deletes and rules |
| --- | --- | --- | --- | --- |
| users | Login accounts | name, email, password, status, email_verified_at, last_login_at | PK id; unique email; index status | Soft delete; password hashed; status enum |
| roles | Role catalog | name, guard_name | PK id; unique name+guard | Spatie-compatible; restricted delete when assigned |
| permissions | Permission catalog | name, guard_name | PK id; unique name+guard | Spatie-compatible |
| model_has_roles | User-role assignments | role_id, model_type, model_id | composite indexes; FK role_id cascade | No soft delete |
| model_has_permissions | Direct model permissions | permission_id, model_type, model_id | composite indexes; FK permission_id cascade | Avoid unless exception needed |
| role_has_permissions | Role permission assignments | permission_id, role_id | composite PK; FKs cascade | No soft delete |
| departments | Hospital departments | code, name, description, status | unique code/name; index status | Soft delete; restrict if employees exist |
| employees | Staff profile | user_id, department_id, employee_number, first_name, last_name, role_title, employment_status, hired_at | unique user_id, employee_number; indexes department_id/status | Soft delete; user nullOnDelete or restrict by policy |
| doctor_schedules | Doctor availability | doctor_employee_id, day_of_week, start_time, end_time, slot_duration_minutes, break times, maximum_appointments, clinic_room, effective dates | FK doctor_employee_id employees; indexes doctor/day/active/effective dates | Soft delete; preserve historical schedules |
| doctor_schedule_exceptions | Schedule exceptions and leave | doctor_employee_id, exception_date, start_time, end_time, exception_type, reason, is_available, maximum_appointments | FK doctor_employee_id employees; index doctor/date | Soft delete; existing appointments are not silently cancelled |
| hospital_services | Billable services | code, name, category, price, is_active | unique code; indexes category/is_active | No hard delete after billing |
| patients | Patient profiles | user_id, patient_number, qr_token, first_name, middle_name, last_name, date_of_birth, sex, contact_number, address, blood_type, status | unique user_id, patient_number, qr_token; indexes name/date_of_birth/status | Soft delete; QR token never stores PHI |
| patient_emergency_contacts | Patient contacts | patient_id, name, relationship, phone, email, address, is_primary | FK patient_id cascade; index patient/is_primary | Cascade with patient |
| patient_allergies | Allergies | patient_id, allergy_type, allergen, reaction, severity, recorded_by, recorded_at | FKs patient/user; index patient/severity | Do not hard delete after clinical use |
| patient_conditions | Conditions | patient_id, condition_name, status, diagnosed_on, resolved_on, recorded_by | FKs patient/user; index patient/status | Preserve history |
| patient_documents | Private files | patient_id, uploaded_by, document_type, title, original_filename, stored_path, mime_type, size_bytes | FKs patient/user; index patient/type | Private storage; validate type/size |
| appointments | Appointment bookings | appointment_number, patient_id, doctor_employee_id, department_id, appointment_type_id, appointment_date, start_time, end_time, duration_minutes, status, source, reasons/notes, actor timestamps, parent_appointment_id | unique appointment_number; indexes patient/date, doctor/date, department/date, status/date | Soft delete; status history required |
| appointment_status_histories | Appointment status audit | appointment_id, old_status, new_status, changed_by, reason | FK appointment; index appointment/created_at | Append-style transition history |
| queues | Visit queue | queue_number, appointment_id, patient_id, doctor_employee_id, department_id, queue_date, status, priority, visit_type, priority flags, timestamps | unique queue_number and appointment_id; indexes department/date/status, doctor/date/status, patient/date | Soft delete; no force-delete route |
| queue_status_histories | Queue status audit | queue_id, old_status, new_status, changed_by, notes | FK queue; index queue/created_at | Append-style transition history |
| triage_records | Triage assessment | queue_id, appointment_id, patient_id, nurse_id, chief_complaint, pain_scale, pregnancy_flag, fall_risk_score, fall_risk_level, acuity, allergy review | FKs queue/appointment/patient/nurse; index patient/created_at | Soft delete; preserve clinical intake |
| vital_signs | Vitals | queue_id, triage_record_id, patient_id, recorded_by, BP, pulse, respiration, temperature, SpO2, height, weight, BMI, measured_at | FKs queue/triage/patient/user; index patient/measured_at | Soft delete; BMI server-calculated |
| diagnosis_catalog | Diagnosis catalog | code, name, description, category, is_active, is_patient_visible_default | unique code; indexes name/category/active | Soft delete; diagnosis snapshots preserve historical text |
| consultations | Doctor encounter | consultation_number, queue_entry_id, appointment_id, patient_id, doctor_employee_id, department_id, status, clinical notes, patient_summary, visibility flags | unique consultation_number; unique queue_entry_id; indexes patient/completed, doctor/status, department/status | Soft delete; completed records are read-only until reopened |
| consultation_diagnoses | Diagnoses per consult | consultation_id, diagnosis_catalog_id, code/name snapshots, type, status, notes, visibility, problem-list sync flag | FKs consultation/catalog/user; index consultation/type | Soft delete; snapshots preserve clinical history |
| patient_problems | Longitudinal problem list | patient_id, diagnosis_catalog_id, source_consultation_diagnosis_id, problem name/code, status, onset/resolution, visibility | indexes patient/status | Soft delete; duplicate active problems prevented in service |
| consultation_attachments | Private clinical files | consultation_id, title, original/stored filename, disk/path, mime, size, confidentiality, visibility | index consultation/visible | Private local storage; controller-mediated download |
| medical_certificates | Doctor-issued certificates | certificate_number, consultation_id, patient_id, doctor_employee_id, status, purpose, summary, recommendation, issue/void metadata | unique certificate_number; indexes patient/status, doctor/status | Soft delete; issue and void are audited |
| specimen_types | Specimen catalog | code, name, instructions, storage requirements, active flag | unique code/name | Soft delete; historical links preserved |
| laboratory_test_categories | Test grouping | code, name, display order, active flag | unique code/name; display order index | Soft delete |
| laboratory_tests | Laboratory test catalog | category, code, name, result type, unit, specimen type, fasting/verifier/panel flags | unique code; category/name/active indexes | Soft delete; inactive tests unavailable for new requests |
| laboratory_test_components | Panel components | parent_test_id, component_test_id, order, required flag | unique parent/component | One-level panel expansion |
| laboratory_reference_ranges | Reference metadata | test, sex, age days, bounds, critical bounds, text reference, unit, effective dates | test/active index | Soft delete; demonstration data unless verified |
| laboratory_requests | Doctor lab request | request_number, consultation, patient, doctor, department, priority, status, clinical info, timestamps | unique request_number; patient/doctor/status indexes | Soft delete; no force-delete route |
| laboratory_request_items | Test items | request, test, snapshots, specimen type, priority, status | request/status index | Soft delete; item-level status |
| laboratory_specimens | Collected specimens | accession_number, request, patient, specimen type, status, actor timestamps, barcode | unique accession/barcode; request/patient/status indexes | Soft delete; rejected accession numbers retained |
| laboratory_specimen_items | Specimen mapping | specimen_id, request_item_id | unique specimen/item | Cross-request mapping blocked by service |
| laboratory_results | Result values | request item, request, patient, test, typed values, range snapshots, abnormal flag, verification/release metadata | unique request item; patient/released indexes | Soft delete; released values amended through history |
| laboratory_result_versions | Amendment history | result, version, snapshot, reason, actor | unique result/version | Append-style amendment history |
| laboratory_result_attachments | Private files | result, title, storage metadata, confidentiality, visibility | result indexes | Private storage |
| laboratory_result_acknowledgments | Doctor review | result, doctor, actor, acknowledged_at | unique result/doctor | Prevent duplicate acknowledgment |
| laboratory_critical_result_logs | Critical handling | result, request, patient, doctor, identified/communicated metadata | doctor/communication index | Internal communication log |
| laboratory_test_definitions | Lab test catalog | code, name, category, sample_type, price, turnaround_hours, is_active | unique code; indexes category/is_active | Restrict when used |
| laboratory_requests | Lab orders | lab_request_number, consultation_id, patient_id, doctor_id, status, requested_at, released_at | unique lab_request_number; indexes patient/status | No hard delete |
| laboratory_request_items | Ordered lab tests | laboratory_request_id, laboratory_test_definition_id, status, specimen_collected_at, notes | FKs request/test; unique request/test | No hard delete |
| laboratory_results | Lab result headers | laboratory_request_item_id, performed_by, verified_by, status, result_summary, released_at | unique request_item_id; indexes status/released_at | No hard delete |
| laboratory_result_items | Detailed lab metrics | laboratory_result_id, analyte, value, unit, reference_range, flag | FK result restrict; index analyte | No hard delete |
| prescriptions | Prescription header | prescription_number, consultation_id, patient_id, doctor_id, status, issued_at, expires_at | unique prescription_number; indexes patient/status | No hard delete |
| prescription_items | Prescribed medicines | prescription_id, medicine_id, dosage, frequency, duration, quantity, instructions | FKs prescription/medicine; index medicine | No hard delete |
| medicine_categories | Medicine grouping | name, description, is_active | unique name | Restrict when used |
| medicines | Medicine master | sku, category_id, generic_name, brand_name, strength, form, reorder_level, is_active | unique sku; indexes category/name | Soft delete; stock from batches and transactions |
| suppliers | Suppliers | name, contact_person, phone, email, address, is_active | unique name/email nullable | Soft delete |
| medicine_batches | Batch stock | medicine_id, supplier_id, batch_number, expiry_date, unit_cost, quantity_received, quantity_available | unique medicine/batch_number; indexes expiry/available | Prevent negative available quantity |
| inventory_transactions | Stock ledger | medicine_id, batch_id, type, quantity, unit_cost, reference_type, reference_id, performed_by, occurred_at, remarks | indexes medicine/batch/type/reference | Append-only; quantity positive with signed type semantics |
| dispensing_records | Dispensing header | dispensing_number, prescription_id, patient_id, pharmacist_id, status, dispensed_at | unique dispensing_number; indexes prescription/status | No hard delete |
| dispensing_items | Dispensed medicine items | dispensing_record_id, prescription_item_id, medicine_batch_id, quantity | FKs dispensing/prescription_item/batch | Quantity cannot exceed available |
| bills | Bill header | bill_number, patient_id, appointment_id, status, subtotal, discount_total, adjustment_total, total, balance, finalized_at | unique bill_number; indexes patient/status | No hard delete; adjustments after finalization |
| bill_items | Line items | bill_id, billable_type, billable_id, description, quantity, unit_price, total | FK bill restrict; polymorphic index | No hard delete |
| bill_adjustments | Financial adjustments | bill_id, adjusted_by, type, amount, reason | FK bill restrict; index bill/type | Append-only |
| payments | Payment records | payment_reference, bill_id, patient_id, cashier_id, amount, method, status, paid_at, verified_at | unique payment_reference; indexes bill/status | No hard delete; refund records instead |
| refunds | Refund-ready records | payment_id, bill_id, amount, reason, status, requested_by, approved_by, refunded_at | FKs payment/bill/user; index status | No hard delete |
| notifications | App notifications | user_id, type, data, read_at | Laravel-compatible; index user/read_at | Keep per retention policy |
| announcements | Announcements | title, body, audience, starts_at, ends_at, created_by, is_active | index audience/dates | Soft delete |
| sms_logs | SMS delivery logs | recipient, message, provider, status, sent_at, error_message, related_type, related_id | index status/related | No hard delete in audit period |
| symptom_checker_sessions | Rule-based symptom sessions | patient_id, symptoms, recommendations, risk_level, completed_at | FK patient restrict; index patient/risk | No diagnosis claim; preserve |
| audit_logs | Immutable audit trail | user_id, action, module, auditable_type, auditable_id, description, old_values, new_values, ip_address, user_agent, created_at | indexes user/action/module/auditable | Append-only; no update/delete routes |
| system_settings | Key-value settings | key, value, type, group, description, is_public, updated_by | unique key; index group | Restricted update; audit changes |

## Reference Number Formats

| Entity | Format |
| --- | --- |
| Patient Number | `PAT-2026-000001` |
| Employee Number | `EMP-2026-000001` |
| Appointment Number | `APT-20260731-0001` |
| Queue Number | `GEN-001` per department/date |
| Consultation Number | `CON-2026-000001` |
| Medical Certificate Number | `MEDCERT-2026-000001` |
| Laboratory Request | `LAB-20260731-0001` |
| Laboratory Accession | `ACC-20260731-00001` |
| Prescription Number | `RX-20260731-0001` |
| Dispensing Number | `DSP-20260731-0001` |
| Bill Number | `BILL-20260731-0001` |
| Payment Reference | `PAY-20260731-0001` |

Collision prevention: lock the sequence row inside a transaction, increment atomically, format with date/year and prefix, insert under a unique constraint, and retry on unique-key conflict.

## Phase 4 Implemented Tables

Phase 4 adds:

- `reference_sequences` for locked reference-number counters.
- `departments` with unique `code` and `name`, nullable department head, status, creator/updater references, timestamps, and soft deletes.
- `employees` with unique `employee_number`, unique `user_id`, department assignment, personal/professional/emergency-contact fields, profile photo path, status/type enums, creator/updater references, timestamps, and soft deletes.
- `audit_logs` for append-only administrative audit records.

The department-head foreign key is intentionally added after `employees` exists to avoid a circular migration dependency.

## Phase 5 Implemented Tables

Phase 5 adds:

- `patients` with unique `patient_number`, unique QR lookup token, optional linked user account, demographic fields, patient status, registration date, creator/updater references, timestamps, and soft deletes.
- `patient_emergency_contacts` for patient contacts with a single primary-contact flag enforced by application logic.
- `patient_allergies` for allergy type, severity, reaction, notes, recorder, and recorded date.
- `patient_conditions` for known conditions, status, diagnosis/resolution dates, notes, and recorder.
- `patient_documents` for private metadata pointing to files in non-public local storage.

## Phase 6 Implemented Tables

Phase 6 adds:

- `appointment_types` with configurable code, name, default duration, approval requirement, active flag, creator/updater references, timestamps, and soft deletes.
- `doctor_schedules` with doctor employee, day of week, working hours, slot duration, break period, daily maximum, clinic room, effective dates, active flag, creator/updater references, timestamps, and soft deletes.
- `doctor_schedule_exceptions` with doctor employee, exception date, optional custom hours, exception type, availability flag, daily maximum override, reason, creator/updater references, timestamps, and soft deletes.
- `appointments` with unique appointment number, patient, doctor employee, department, appointment type, date/time range, duration, status, source, notes/reasons, status actor timestamps, parent reschedule link, creator/updater references, timestamps, and soft deletes.
- `appointment_status_histories` for appointment transition history.

## Phase 7 Implemented Tables

Phase 7 adds:

- `queues` for appointment check-in and walk-in operational queues.
- `queue_status_histories` for queue status transitions.
- `triage_records` for nurse clinical intake data.
- `vital_signs` for BP, pulse, respiration, temperature, oxygen saturation, height, weight, and BMI.

## Phase 8 Implemented Tables

Phase 8 adds:

- `diagnosis_catalog` for reusable diagnosis metadata.
- `consultations` for doctor encounter records linked to queue and appointment workflows.
- `consultation_diagnoses` for encounter-specific diagnosis snapshots.
- `patient_problems` for longitudinal problem-list sync.
- `consultation_attachments` for private clinical file metadata.
- `medical_certificates` for draft, issued, and void certificate records.

## Phase 9 Implemented Tables

Phase 9 adds laboratory catalog, request, specimen, result, amendment, attachment, acknowledgment, and critical-result communication tables. Result values are stored as typed columns with reference-range snapshots and patient visibility controls.

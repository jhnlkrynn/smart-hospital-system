# Database Design Plan

Use `id` primary keys unless a future integration justifies UUIDs. Patient QR codes must store secure random lookup tokens, not raw patient data. Monetary values use `decimal(12,2)` or more precision where needed. Dates without times use `date`; workflow moments use nullable timestamps. Do not store calculated age.

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
| doctor_schedules | Doctor availability | doctor_id, day_of_week, starts_at, ends_at, slot_minutes, room, max_patients, effective_from, effective_until | FK doctor_id employees; index doctor/day; unique doctor/day/start/end/effective_from | Restrict delete after appointments |
| doctor_unavailable_dates | Schedule exceptions | doctor_id, unavailable_date, reason | unique doctor/date; FK doctor_id | Restrict/delete only future unused |
| hospital_services | Billable services | code, name, category, price, is_active | unique code; indexes category/is_active | No hard delete after billing |
| patients | Patient profiles | user_id, patient_number, qr_token_hash, first_name, last_name, date_of_birth, sex, contact_number, address, blood_type | unique user_id, patient_number, qr_token_hash; indexes name/date_of_birth | Soft delete; QR token never stores PHI |
| patient_emergency_contacts | Patient contacts | patient_id, name, relationship, phone, address, is_primary | FK patient_id cascade; index patient/is_primary | Cascade with patient only before real records |
| patient_allergies | Allergies | patient_id, allergen, reaction, severity, noted_at | FK patient_id restrict; index patient/allergen | Do not hard delete after clinical use |
| patient_conditions | Conditions | patient_id, condition_name, status, diagnosed_on, notes | FK patient_id restrict; index patient/status | Preserve history |
| patient_documents | Private files | patient_id, uploaded_by, file_name, path, mime_type, size, visibility | FKs patient/user; index patient/visibility | Private storage; validate type/size |
| appointments | Appointment bookings | appointment_number, patient_id, doctor_id, schedule_id, scheduled_date, starts_at, ends_at, status, reason, booked_by | unique appointment_number; unique doctor/date/start unless cancelled; indexes patient/doctor/status/date | No hard delete; status history required |
| appointment_status_histories | Appointment status audit | appointment_id, old_status, new_status, changed_by, reason | FK appointment restrict; index appointment/created_at | Append-only |
| queues | Visit queue | queue_number, appointment_id, patient_id, department_id, status, priority, called_at, completed_at | unique department/date/queue_number; indexes status/department | No hard delete |
| queue_status_histories | Queue status audit | queue_id, old_status, new_status, changed_by, notes | FK queue restrict; index queue/created_at | Append-only |
| triage_records | Triage assessment | appointment_id, patient_id, nurse_id, category, chief_complaint, notes | FKs appointment/patient/nurse; index category | No hard delete |
| vital_signs | Vitals | patient_id, appointment_id, recorded_by, temperature, blood_pressure_systolic, blood_pressure_diastolic, pulse, respiration, oxygen_saturation, weight, height, recorded_at | FKs patient/appointment/user; index patient/recorded_at | No hard delete |
| consultations | Doctor encounter | consultation_number, appointment_id, patient_id, doctor_id, status, subjective, objective, assessment, plan, finalized_at | unique consultation_number; unique appointment_id; indexes doctor/status | Finalized records require amendments |
| medical_records | Longitudinal record entries | patient_id, consultation_id, record_type, summary, details, created_by, finalized_at | FKs patient/consultation/user; index patient/type | No hard delete |
| medical_record_amendments | Amendments | medical_record_id, amended_by, reason, old_values, new_values | FK medical_record restrict; index record | Append-only |
| diagnoses | Diagnosis catalog | code, name, description, is_active | unique code; index name | Restrict when used |
| consultation_diagnoses | Diagnoses per consult | consultation_id, diagnosis_id, type, notes | composite unique consultation/diagnosis/type | No soft delete |
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
| Consultation Number | `CON-20260731-0001` |
| Laboratory Request | `LAB-20260731-0001` |
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

# Role Permission Matrix

The canonical implementation lives in `app/Support/AccessControl.php`.

| Role | Permission scope |
| --- | --- |
| Super Admin | All permissions. |
| Hospital Admin | Hospital administration dashboard, users view/create/update, departments, employees, patients administration, doctor schedules, appointment administration, queue viewing, announcements, administrative reports, and settings view. No unrestricted role/permission management or audit-log access. |
| Doctor | Doctor dashboard, own schedule, assigned appointments, assigned consultations, authorized patient records, medical records for care delivery, diagnoses, lab requests/results, prescriptions, and notifications. No inventory, payments, user roles, or settings. |
| Nurse | Nurse dashboard, patient registration support, appointment check-in, queue management, triage, vital signs, limited patient viewing, and notifications. No billing administration, role management, or unrestricted consultation notes. |
| Patient | Patient dashboard, own patient record, own appointments, own queue status, own released lab results, own prescriptions, own bills/payments, own notifications, and symptom checker. No other patient access. |
| Pharmacist | Pharmacist dashboard, prescriptions needed for dispensing, medicines, batches, suppliers, inventory, dispensing, inventory reports, and notifications. No complete consultation notes. |
| Laboratory Staff | Laboratory dashboard, test definitions, laboratory requests, specimen processing, result entry/update/release, clinical lab reports, and notifications. No unrelated medical or financial records. |
| Cashier | Cashier dashboard, bills, payments, receipt printing, financial reports, and notifications. No detailed medical records, consultation notes, or prescription administration. |

## Phase 4 Employee Profile Access

Doctors, nurses, pharmacists, laboratory staff, cashiers, hospital admins, and super admins may view their own employee profile through `/profile/employment`. This own-profile route does not grant employee-management permissions. Patient users have no employee-management access.

## Phase 5 Patient Access

Patient management is split between administrative registration, patient self-service, and QR lookup. Super Admin and Hospital Admin manage all patient records. Nurses can register and update patient profiles for intake support. Doctors can view identity and manage allergy or condition facts needed for care. Pharmacists and Laboratory Staff can use QR lookup and view identity for future care workflows. Cashiers can view basic patient identity only. Patients can view and update their own non-clinical profile and view their own QR card.

## Phase 6 Schedule and Appointment Access

Super Admin has full schedule and appointment access. Hospital Admin can manage doctor schedules, exceptions, appointment types, and all appointments without gaining protected Super Admin account privileges. Doctors can view their own schedule and assigned appointments, and can approve, reject, complete, or mark assigned appointments no-show where permitted. Nurses can view schedules and create/reschedule/cancel patient appointments. Patients can book, view, cancel, and reschedule only their own appointments. Pharmacists, Laboratory Staff, and Cashiers do not receive appointment-management access by default.

## Phase 7 Queue and Triage Access

Nurses manage queue check-in, walk-ins, calling, triage, and vital-sign recording. Doctors view and complete their own assigned doctor queue. Patients view only their own queue status. Queue numbers, queue priorities, status timestamps, triage actors, and BMI are generated or enforced server-side.

## Phase 8 Clinical Record Access

Doctors start and complete assigned consultations, manage diagnoses, upload clinical attachments, and issue medical certificates for their own encounter records. Hospital Admins can view and reopen consultation records for operational review but cannot edit doctor-owned notes. Patients can view only their own finalized, patient-visible EMR timeline and issued certificates. Pharmacists, Laboratory Staff, and Cashiers do not receive detailed consultation access.

## Phase 9 Laboratory Access

Hospital Admins and Super Admins can manage laboratory catalog metadata. Doctors create requests from assigned consultations and review released assigned results, but cannot enter, verify, or release values. Laboratory Staff can collect specimens, enter results, verify, release, amend, and upload result attachments according to assigned permissions. Patients can view only their own released patient-visible results. Cashiers and Pharmacists do not receive full laboratory workflow access.

## Sensitive Permission Checks

| Permission | Super Admin | Hospital Admin | Doctor | Nurse | Patient | Pharmacist | Laboratory Staff | Cashier |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `roles.manage` | Yes | No | No | No | No | No | No | No |
| `permissions.manage` | Yes | No | No | No | No | No | No | No |
| `patients.view-medical-records` | Yes | No | Yes, assigned | No | No | No | No | No |
| `patients.manage-documents` | Yes | Yes | No | Yes | No | No | No | No |
| `patients.download-documents` | Yes | Yes | No | No | No | No | No | No |
| `patients.lookup-qr` | Yes | Yes | Yes | Yes | No | Yes | Yes | No |
| `patients.view-qr` | Yes | Yes | Yes | Yes | Yes, own | Yes | No | No |
| `doctor-schedules.manage-all` | Yes | Yes | No | No | No | No | No | No |
| `doctor-schedules.manage-own` | Yes | No | Yes | No | No | No | No | No |
| `appointments.manage-all` | Yes | Yes | No | No | No | No | No | No |
| `appointments.view-assigned` | Yes | No | Yes | No | No | No | No | No |
| `appointments.view-own` | Yes | No | No | No | Yes | No | No | No |
| `medical-records.view` | Yes | No | Yes, assigned | No | No | No | No | No |
| `medical-records.view-own` | Yes | No | No | No | Yes | No | No | No |
| `consultations.start` | Yes | No | Yes, assigned | No | No | No | No | No |
| `consultations.view-all` | Yes | Yes | No | No | No | No | No | No |
| `diagnoses.manage-catalog` | Yes | Yes | No | No | No | No | No | No |
| `clinical-attachments.download` | Yes | No | Yes, assigned | No | No | No | No | No |
| `medical-certificates.issue` | Yes | No | Yes, assigned | No | No | No | No | No |
| `payments.verify` | Yes | No | No | No | No | No | No | Yes |
| `payments.refund` | Yes | No | No | No | No | No | No | Yes |
| `inventory.adjust` | Yes | No | No | No | No | Yes | No | No |
| `laboratory-results.release` | Yes | No | No | No | No | No | Yes | No |
| `audit-logs.view` | Yes | No | No | No | No | No | No | No |

Run `php artisan db:seed` repeatedly without creating duplicates.

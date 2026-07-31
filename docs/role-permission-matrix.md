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

## Sensitive Permission Checks

| Permission | Super Admin | Hospital Admin | Doctor | Nurse | Patient | Pharmacist | Laboratory Staff | Cashier |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `roles.manage` | Yes | No | No | No | No | No | No | No |
| `permissions.manage` | Yes | No | No | No | No | No | No | No |
| `patients.view-medical-records` | Yes | No | Yes, assigned | No | No | No | No | No |
| `medical-records.view` | Yes | No | Yes, assigned | No | No | No | No | No |
| `medical-records.view-own` | Yes | No | No | No | Yes | No | No | No |
| `payments.verify` | Yes | No | No | No | No | No | No | Yes |
| `payments.refund` | Yes | No | No | No | No | No | No | Yes |
| `inventory.adjust` | Yes | No | No | No | No | Yes | No | No |
| `laboratory-results.release` | Yes | No | No | No | No | No | Yes | No |
| `audit-logs.view` | Yes | No | No | No | No | No | No | No |

Run `php artisan db:seed` repeatedly without creating duplicates.

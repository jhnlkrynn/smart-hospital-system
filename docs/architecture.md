# Smart Hospital Management System Architecture

## Existing Project Inspection

Inspection was performed before Phase 2 planning. Secrets from `.env` were not copied into documentation.

| Area | Current state |
| --- | --- |
| Laravel | 12.64.0 |
| PHP | 8.2.12 CLI |
| Authentication | Laravel Breeze 2.4.2 with Blade, Tailwind, email verification routes, profile management, password reset, and registration |
| Routes | `routes/web.php`, `routes/auth.php`, and `routes/console.php`; Laravel 12 route registration in `bootstrap/app.php` |
| Migrations | Starter `users`, `cache`, and `jobs` migrations; all ran in batch 1 |
| Models | `App\Models\User` only |
| Controllers | Base controller, Breeze auth controllers, and `ProfileController` |
| Views | Breeze `auth`, `components`, `layouts`, `profile`, `dashboard.blade.php`, and `welcome.blade.php` |
| Composer packages | Laravel framework, Tinker; dev packages include Breeze, Pail, Pint, Sail, Collision, Faker, Mockery, PHPUnit |
| NPM packages | Tailwind CSS, Tailwind forms, Alpine.js, Axios, Vite, Laravel Vite plugin, PostCSS, Autoprefixer, Concurrently |
| Database config | Uses env-driven connection settings; `DB_*` keys exist and are intentionally redacted |
| Git status | Clean before Phase 2 edits |

## Architecture Summary

The application will use a modular Laravel monolith. The first production boundary is module and authorization design, not separate services. This keeps the portfolio application maintainable while preserving clean seams for future API, queue, SMS, and reporting work.

### Presentation Layer

- Laravel Blade views for server-rendered pages.
- Tailwind CSS for layout and styling.
- Alpine.js for small interactive controls.
- Chart.js for dashboards and reports.
- Reusable Blade components for forms, tables, filters, status badges, alerts, and modal flows.
- Responsive role dashboards for Super Admin, Hospital Admin, Doctor, Nurse, Patient, Pharmacist, Laboratory Staff, and Cashier.

### Application Layer

- Controllers receive HTTP requests and coordinate responses.
- Form Requests validate input and may perform request-level authorization.
- API Resources shape future REST API responses.
- Middleware enforces authentication, verification, roles, rate limits, and contextual access.
- Policies enforce record-level access.
- Notifications, events, and listeners handle cross-module side effects.

### Business Logic Layer

- Service classes own workflow logic that should not live inside controllers.
- PHP enums define valid workflow statuses.
- Actions may be introduced for focused single-purpose operations.
- Database transactions protect multi-write workflows.
- Workflow validation prevents invalid status transitions.
- Audit logging records sensitive and regulated actions.

### Data Layer

- Eloquent models represent tables and relationships.
- MySQL or MariaDB is the planned database.
- Migrations, seeders, and factories will be added phase by phase.
- Query scopes will support common filters such as active, assigned, pending, released, paid, and date ranges.

## Request Flow

```text
User Request
-> Route
-> Middleware
-> Controller
-> Form Request Validation
-> Policy Authorization
-> Service Class
-> Eloquent Models
-> Database
-> Response or Blade View
```

Complex operations such as appointment scheduling, laboratory processing, medicine dispensing, billing, and payments must use service classes with database transactions. These workflows update multiple tables, generate reference numbers, enforce status transitions, and often create audit records. A transaction prevents partial state, such as a payment without a bill update or a dispensing record without a matching inventory transaction.

## User Roles

| Role | Responsibilities |
| --- | --- |
| Super Admin | Full system access; manage administrators, users, roles, permissions, settings, reports, and audit logs. |
| Hospital Admin | Manage departments, employees, patients, doctor schedules, hospital services, announcements, and administrative reports. |
| Doctor | View assigned appointments and authorized patient data; conduct consultations; create diagnoses, laboratory requests, and prescriptions; view released lab results. |
| Nurse | Register patients, check in patients, manage queues, record triage and vital signs, and assist with appointments. |
| Patient | Manage own profile; book appointments; view queue status, released laboratory results, prescriptions, bills, payments, QR patient ID, and symptom checker sessions. |
| Pharmacist | Manage medicines, medicine batches, inventory, prescriptions for dispensing, low-stock items, and expiring medicines. |
| Laboratory Staff | Process lab requests, record specimen collection, enter results, complete results, and release results according to policy. |
| Cashier | Manage bills, record payments, process partial payments, print receipts, and view collection reports. |

## Laravel Structure Plan

```text
app/
|-- Enums/
|-- Events/
|-- Exceptions/
|-- Http/
|   |-- Controllers/
|   |   |-- Admin/
|   |   |-- Api/
|   |   |-- Cashier/
|   |   |-- Doctor/
|   |   |-- Laboratory/
|   |   |-- Nurse/
|   |   |-- Patient/
|   |   `-- Pharmacist/
|   |-- Middleware/
|   |-- Requests/
|   `-- Resources/
|-- Listeners/
|-- Models/
|-- Notifications/
|-- Policies/
|-- Services/
|   |-- AI/
|   |-- Appointment/
|   |-- Audit/
|   |-- Billing/
|   |-- Inventory/
|   |-- Laboratory/
|   |-- Payment/
|   |-- Queue/
|   |-- QR/
|   `-- SMS/
`-- Support/
```

Major folder purposes:

- `Enums`: typed workflow states and transaction types.
- `Events` and `Listeners`: cross-module side effects such as notifications and audit entries.
- `Http/Controllers`: thin HTTP controllers grouped by role or API surface.
- `Http/Middleware`: authentication, verification, role, permission, throttling, and contextual checks.
- `Http/Requests`: validation and request authorization.
- `Http/Resources`: future Sanctum API response transformers.
- `Models`: Eloquent models and relationships.
- `Notifications`: email, database, and future SMS-ready notifications.
- `Policies`: record-level authorization.
- `Services`: transactional business workflows.
- `Support`: shared helpers such as value objects, reference formats, and constants.

## Blade Structure Plan

```text
resources/views/
|-- layouts/
|-- components/
|-- auth/
|-- dashboards/
|-- admin/
|-- doctor/
|-- nurse/
|-- patient/
|-- pharmacist/
|-- laboratory/
|-- cashier/
|-- departments/
|-- employees/
|-- patients/
|-- appointments/
|-- queues/
|-- triage/
|-- consultations/
|-- laboratory-requests/
|-- laboratory-results/
|-- prescriptions/
|-- pharmacy/
|-- billing/
|-- payments/
|-- reports/
|-- announcements/
|-- settings/
|-- audit-logs/
`-- errors/
```

Role folders contain dashboards and role-specific workflows. Module folders contain shared CRUD and workflow screens. `components` should hold reusable fields, tables, filters, status badges, and action menus. `layouts` should evolve from Breeze layouts into role-aware shells.

## Naming Conventions

| Item | Convention | Example |
| --- | --- | --- |
| Database table | plural snake_case | `laboratory_requests` |
| Model | singular StudlyCase | `LaboratoryRequest` |
| Controller | singular/resource StudlyCase + Controller | `LaboratoryRequestController` |
| Policy | model name + Policy | `LaboratoryRequestPolicy` |
| Service | domain + Service | `LaboratoryRequestService` |
| Form Request | action + model + Request | `StoreLaboratoryRequest` |
| Route name | kebab resource + action | `laboratory-requests.store` |
| Role route name | role prefix + resource/action | `doctor.appointments.index` |
| Blade view | kebab folder + file | `laboratory-requests/create.blade.php` |
| Permission | resource.action | `laboratory-requests.create` |
| Enum case value | lower snake_case | `specimen_collected` |
| Reference number | uppercase prefix + date/year + sequence | `LAB-20260731-0001` |
| Git branch | type/short-description | `docs/phase-2-architecture` |
| Git commit | conventional commit | `docs: add system architecture and database plan` |

## Status Enum Plan

| File | Values |
| --- | --- |
| `app/Enums/UserStatus.php` | `active`, `inactive`, `suspended`, `locked` |
| `app/Enums/EmploymentStatus.php` | `active`, `on_leave`, `inactive`, `terminated` |
| `app/Enums/AppointmentStatus.php` | `pending`, `confirmed`, `checked_in`, `in_queue`, `in_consultation`, `completed`, `cancelled`, `rescheduled`, `no_show` |
| `app/Enums/QueueStatus.php` | `waiting`, `called`, `serving`, `skipped`, `transferred`, `completed`, `cancelled` |
| `app/Enums/TriageCategory.php` | `emergency`, `urgent`, `priority`, `standard` |
| `app/Enums/ConsultationStatus.php` | `draft`, `in_progress`, `finalized`, `amended` |
| `app/Enums/LaboratoryRequestStatus.php` | `requested`, `accepted`, `specimen_collected`, `processing`, `completed`, `released`, `cancelled` |
| `app/Enums/PrescriptionStatus.php` | `active`, `partially_dispensed`, `dispensed`, `cancelled`, `expired` |
| `app/Enums/InventoryTransactionType.php` | `stock_in`, `stock_out`, `adjustment`, `transfer`, `dispensed`, `damaged`, `expired`, `returned` |
| `app/Enums/BillStatus.php` | `draft`, `pending`, `partially_paid`, `paid`, `cancelled`, `refunded` |
| `app/Enums/PaymentStatus.php` | `pending_verification`, `verified`, `failed`, `cancelled`, `refunded` |

## Model and Relationship Plan

| Model | Table | Strategy, casts, scopes, relationships, policy |
| --- | --- | --- |
| User | users | Fillable for account fields; casts status enum, email timestamp, hashed password; scopes active/inactive; hasOne Employee, hasOne Patient, morph role assignments; `UserPolicy`. |
| Department | departments | Guarded id; casts active/status; scopes active; hasMany Employees; soft deletes; `DepartmentPolicy`. |
| Employee | employees | Guarded id; casts employment status/date fields; belongsTo User and Department; hasMany doctor schedules and doctor appointments; soft deletes; `EmployeePolicy`. |
| Patient | patients | Guarded id; casts date_of_birth; belongsTo User; hasMany contacts, allergies, conditions, documents, appointments, bills; soft deletes; `PatientPolicy`. |
| DoctorSchedule | doctor_schedules | Guarded id; casts time/date fields; belongsTo doctor Employee; hasMany Appointments; scopes for doctor/date; schedule authorization. |
| Appointment | appointments | Guarded id; casts status enum/date/time/timestamps; belongsTo Patient, doctor Employee, DoctorSchedule; hasOne Queue, TriageRecord, Consultation; hasMany histories; `AppointmentPolicy`. |
| Queue | queues | Guarded id; casts status enum/timestamps; belongsTo Appointment, Patient, Department; hasMany histories; scopes waiting/current; `QueuePolicy`. |
| TriageRecord | triage_records | Guarded id; casts triage category; belongsTo Appointment, Patient, nurse Employee; relates to VitalSign by appointment; `TriageRecordPolicy`. |
| VitalSign | vital_signs | Guarded id; decimal/integer casts; belongsTo Patient, Appointment, recorder User; scopes latestForPatient; policy through triage/consultation access. |
| Consultation | consultations | Guarded id; casts consultation status/finalized_at; belongsTo Appointment, Patient, doctor Employee; hasMany diagnoses, lab requests, prescriptions; `ConsultationPolicy`. |
| MedicalRecord | medical_records | Guarded id; casts finalized_at; belongsTo Patient and Consultation; hasMany amendments; no hard delete; `MedicalRecordPolicy`. |
| Diagnosis | diagnoses | Guarded id; casts is_active; belongsToMany Consultations; scopes active; restrict delete; admin catalog authorization. |
| LaboratoryRequest | laboratory_requests | Guarded id; casts status enum/timestamps; belongsTo Consultation, Patient, doctor; hasMany items; `LaboratoryRequestPolicy`. |
| LaboratoryResult | laboratory_results | Guarded id; casts status/released_at; belongsTo request item and staff; hasMany result items; `LaboratoryResultPolicy`. |
| Prescription | prescriptions | Guarded id; casts prescription status/date fields; belongsTo Consultation, Patient, doctor; hasMany items and dispensing records; `PrescriptionPolicy`. |
| Medicine | medicines | Guarded id; casts reorder level/is_active; belongsTo category; hasMany batches, prescription items, inventory transactions; soft deletes; `MedicinePolicy`. |
| MedicineBatch | medicine_batches | Guarded id; decimal/integer/date casts; belongsTo Medicine and Supplier; hasMany dispensing items and inventory transactions; batch access via medicine policy. |
| InventoryTransaction | inventory_transactions | Guarded id; casts transaction type, quantity, occurred_at; belongsTo Medicine, batch, user; append-only; `InventoryTransactionPolicy`. |
| DispensingRecord | dispensing_records | Guarded id; casts dispensed_at/status; belongsTo Prescription, Patient, pharmacist; hasMany items; dispensing authorization. |
| Bill | bills | Guarded id; decimal casts and status enum; belongsTo Patient/Appointment; hasMany items, adjustments, payments; no hard delete; `BillPolicy`. |
| Payment | payments | Guarded id; decimal/status/date casts; belongsTo Bill, Patient, Cashier; hasMany refunds; no hard delete; `PaymentPolicy`. |
| Announcement | announcements | Guarded id; casts date windows/is_active; belongsTo creator User; soft deletes; announcement policy. |
| AuditLog | audit_logs | Guarded all; json casts old/new values; belongsTo User; morphs auditable; append-only; `AuditLogPolicy`. |
| SystemSetting | system_settings | Guarded id; casts typed values through accessor; belongsTo updater User; scopes group/public; `SystemSettingPolicy`. |

## Service Class Plan

| Service | Responsibility and methods | Models | Transaction |
| --- | --- | --- | --- |
| RoleAssignmentService | Assign, sync, remove roles; protect last super admin. | User, roles | Yes |
| ReferenceNumberService | Generate unique references by prefix/date/year with locked sequence. | all referenced entities | Yes |
| AppointmentAvailabilityService | Find slots and detect conflicts. | DoctorSchedule, Appointment | No for reads; yes when reserving |
| AppointmentBookingService | Book, confirm, cancel, reschedule appointments. | Appointment, histories | Yes |
| QueueNumberService | Generate per-department queue numbers. | Queue | Yes |
| QueueManagementService | Call, transfer, skip, complete queues. | Queue, histories | Yes |
| TriageService | Record triage and vital signs. | TriageRecord, VitalSign | Yes |
| ConsultationService | Start, update, finalize, amend consultations. | Consultation, MedicalRecord, Diagnosis | Yes |
| MedicalRecordService | Create final records and amendments. | MedicalRecord, amendments | Yes |
| LaboratoryRequestService | Create/cancel lab requests and items. | LaboratoryRequest, items | Yes |
| LaboratoryResultService | Collect specimen, enter, complete, release results. | Lab items/results | Yes |
| PrescriptionService | Create/cancel prescriptions and items. | Prescription, items | Yes |
| InventoryService | Stock in, adjust, transfer, expire batches. | Medicine, batches, transactions | Yes |
| DispensingService | Dispense prescriptions and reduce batch stock. | DispensingRecord, items, batches | Yes |
| BillingService | Create bills, add items, finalize, adjust balances. | Bill, items, adjustments | Yes |
| PaymentService | Record, verify, refund payments. | Payment, Refund, Bill | Yes |
| QRCodeService | Issue and rotate secure patient QR tokens. | Patient | Yes |
| NotificationService | Send database/email notifications and prepare SMS jobs. | User, notifications | Usually |
| AuditLogService | Record immutable audit events. | AuditLog | Same transaction when possible |
| SymptomCheckerService | Rule-based symptom guidance with disclaimers. | SymptomCheckerSession | Yes when saving |
| SmsServiceInterface | Contract for SMS providers. | SmsLog | Implementation dependent |
| MockSmsService | Local SMS simulator for portfolio/demo. | SmsLog | Yes when logging |

## Policy and Authorization Plan

Policies required: `UserPolicy`, `DepartmentPolicy`, `EmployeePolicy`, `PatientPolicy`, `AppointmentPolicy`, `QueuePolicy`, `TriageRecordPolicy`, `ConsultationPolicy`, `MedicalRecordPolicy`, `LaboratoryRequestPolicy`, `LaboratoryResultPolicy`, `PrescriptionPolicy`, `MedicinePolicy`, `InventoryTransactionPolicy`, `BillPolicy`, `PaymentPolicy`, `ReportPolicy`, `AuditLogPolicy`, and `SystemSettingPolicy`.

Record-level examples:

- Patients view only their own records, bills, payments, prescriptions, released results, and QR profile.
- Doctors view patients only when assigned through appointment, consultation, prescription, or laboratory request.
- Nurses receive queue, triage, and vital-sign access, with limited clinical context.
- Pharmacists see prescription and dispensing context but not full consultation notes.
- Cashiers see billable descriptions, totals, payments, and receipts but not detailed medical records.
- Laboratory staff see lab request context, specimen details, and result fields only.

## Form Request Plan

Planned requests include `StoreDepartmentRequest`, `UpdateDepartmentRequest`, `StoreEmployeeRequest`, `UpdateEmployeeRequest`, `StorePatientRequest`, `UpdatePatientRequest`, `StoreDoctorScheduleRequest`, `StoreAppointmentRequest`, `RescheduleAppointmentRequest`, `StoreTriageRequest`, `StoreVitalSignRequest`, `StoreConsultationRequest`, `FinalizeConsultationRequest`, `StoreLaboratoryRequest`, `StoreLaboratoryResultRequest`, `StorePrescriptionRequest`, `StoreMedicineRequest`, `StoreInventoryTransactionRequest`, `DispensePrescriptionRequest`, `StoreBillRequest`, and `StorePaymentRequest`.

Each request should validate input shape and call authorization rules in `authorize()` where the decision is request-specific. Policies remain the source of truth for record-level authorization.

## Documentation Index

- [Database Design](database-design.md)
- [Entity Relationship Diagram](entity-relationship-diagram.md)
- [Module Dependencies](module-dependencies.md)
- [Permission Matrix](permission-matrix.md)
- [Route Plan](route-plan.md)
- [Security Plan](security-plan.md)
- [Development Roadmap](development-roadmap.md)

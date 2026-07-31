# Dashboard Routing

Laravel 12 registers the Phase 3 role route files in `bootstrap/app.php` through `withRouting(... then: ...)`.

| URI | Route name | Controller | Role |
| --- | --- | --- | --- |
| `/dashboard` | `dashboard` | `DashboardRedirectController` | Any authenticated active user |
| `/super-admin/dashboard` | `super-admin.dashboard` | `SuperAdminDashboardController` | `super-admin` |
| `/admin/dashboard` | `admin.dashboard` | `HospitalAdminDashboardController` | `hospital-admin` |
| `/doctor/dashboard` | `doctor.dashboard` | `DoctorDashboardController` | `doctor` |
| `/nurse/dashboard` | `nurse.dashboard` | `NurseDashboardController` | `nurse` |
| `/patient/dashboard` | `patient.dashboard` | `PatientDashboardController` | `patient` |
| `/pharmacist/dashboard` | `pharmacist.dashboard` | `PharmacistDashboardController` | `pharmacist` |
| `/laboratory/dashboard` | `laboratory.dashboard` | `LaboratoryDashboardController` | `laboratory-staff` |
| `/cashier/dashboard` | `cashier.dashboard` | `CashierDashboardController` | `cashier` |
| `/account/pending` | `account.pending` | view route | authenticated active users without roles |

Every role dashboard is protected by `auth`, `verified`, `account.active`, `role`, and `permission` middleware. The `/dashboard` route redirects users through `RoleRedirectService`.

If dashboard access returns unexpected 403 responses, run:

```bash
php artisan permission:cache-reset
php artisan optimize:clear
php artisan db:seed
```

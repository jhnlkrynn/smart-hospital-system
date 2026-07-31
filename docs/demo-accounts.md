# Demo Accounts

These fictional accounts are for local development only. Remove them or change all credentials before deployment.

Development password for all demo users:

```text
Password123!
```

| Role | Name | Email | Dashboard |
| --- | --- | --- | --- |
| Super Admin | Samantha Reyes | `superadmin@hospital.test` | `/super-admin/dashboard` |
| Hospital Admin | Adrian Santos | `admin@hospital.test` | `/admin/dashboard` |
| Doctor | Dr. Miguel Navarro | `doctor@hospital.test` | `/doctor/dashboard` |
| Nurse | Angela Cruz | `nurse@hospital.test` | `/nurse/dashboard` |
| Patient | Paolo Mendoza | `patient@hospital.test` | `/patient/dashboard` |
| Pharmacist | Rica Villanueva | `pharmacist@hospital.test` | `/pharmacist/dashboard` |
| Laboratory Staff | Daniel Garcia | `laboratory@hospital.test` | `/laboratory/dashboard` |
| Cashier | Marianne Flores | `cashier@hospital.test` | `/cashier/dashboard` |

Seeder command:

```bash
php artisan db:seed
```

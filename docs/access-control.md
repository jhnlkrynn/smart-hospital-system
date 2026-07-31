# Access Control

Phase 3 uses Spatie Laravel Permission `6.25.0` with the default `web` guard. Role slugs are the only values used in authorization checks.

## Roles

| Slug | Label |
| --- | --- |
| `super-admin` | Super Admin |
| `hospital-admin` | Hospital Admin |
| `doctor` | Doctor |
| `nurse` | Nurse |
| `patient` | Patient |
| `pharmacist` | Pharmacist |
| `laboratory-staff` | Laboratory Staff |
| `cashier` | Cashier |

## Implementation Files

- `app/Support/AccessControl.php` is the source of truth for roles, permissions, assignments, dashboards, and demo users.
- `database/seeders/RoleSeeder.php` creates roles idempotently.
- `database/seeders/PermissionSeeder.php` creates permissions idempotently.
- `database/seeders/RolePermissionSeeder.php` syncs least-privilege assignments.
- `database/seeders/DemoUserSeeder.php` creates fictional development users.
- `app/Http/Middleware/EnsureAccountIsActive.php` logs out blocked users safely.
- `app/Services/Auth/RoleRedirectService.php` maps roles to dashboard routes.

## Account Status

The `users` table now includes `status`, `last_login_at`, `last_login_ip`, `failed_login_attempts`, `locked_until`, `deactivated_at`, and `deactivated_by`.

Allowed `UserStatus` values:

- `active`
- `inactive`
- `suspended`
- `locked`

Inactive and suspended accounts cannot log in. Locked accounts cannot log in while the lock is active; a lock with an expired `locked_until` is allowed to proceed.

## Login and Registration Rules

- Breeze authentication remains in place.
- Successful login regenerates the session.
- Successful login records last login timestamp/IP and resets failed attempts.
- Failed password login increments failed attempts without revealing whether the email exists.
- Public registration always assigns the `patient` role.
- Public registration never accepts staff or administrator role input from the browser.

## Super Admin Protection

- Seeders ensure at least one Super Admin demo user exists.
- Hospital Admins do not receive role or permission management permissions.
- Public registration can never assign `super-admin`.
- Future user-management screens must prevent deactivating the last active Super Admin.
- Future role-management screens must prevent non-Super Admin users from modifying Super Admin permissions.

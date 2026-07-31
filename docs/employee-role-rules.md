# Employee Role Rules

## Assignable Roles

Hospital Admins may assign:

- `hospital-admin`
- `doctor`
- `nurse`
- `pharmacist`
- `laboratory-staff`
- `cashier`

Super Admins may assign those roles plus:

- `super-admin`

The `patient` role is not valid for employee accounts.

## Protected Accounts

- Hospital Admins cannot create, update, or archive Super Admin employee accounts.
- Super Admin employee accounts may only be managed by Super Admin users.
- The final active Super Admin must not be archived.
- Public registration still creates Patient users only.

## Role-Specific Validation

- Doctor employees require `specialization`.
- Doctor consultation fees must be non-negative.
- License fields are available for doctors, nurses, pharmacists, and laboratory staff.
- Future phases may tighten license requirements by role.

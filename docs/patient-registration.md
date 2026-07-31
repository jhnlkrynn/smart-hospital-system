# Patient Registration

Public registration now creates both a `users` account and a linked `patients` profile.

## Public Registration

The `/register` form collects:

- First name, optional middle name, and last name.
- Date of birth.
- Sex.
- Contact number.
- Email address.
- Password.

The server always assigns the `patient` role. Browser-submitted role values are ignored, so public users cannot self-assign administrator or staff roles.

## Staff Registration

Authorized staff use `/admin/patients/create`. Staff may create a patient profile with or without a login account.

When `create_account` is enabled:

- Email is required and must be unique among users and patients.
- A temporary password is required.
- The new user is assigned the `patient` role.
- The patient profile is linked through `patients.user_id`.

When `create_account` is disabled:

- Contact number is required.
- Email is optional.
- No login account is created.

## Patient Self-Service

Patients can view and update their own profile at `/patient/profile`. Self-service updates are limited to contact and non-clinical demographics. Patients cannot change their patient number, QR token, status, blood type, allergies, conditions, or uploaded documents through the self-profile form.

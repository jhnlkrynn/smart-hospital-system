# Security and Privacy Plan

This is a portfolio application architecture. It is not automatically compliant with healthcare laws or institutional policy. Before use with real patient data, it must undergo legal, security, privacy, infrastructure, and operational review.

## Required Rules

- Enforce role-based authorization through middleware and permissions.
- Enforce record-level authorization through policies.
- Keep CSRF protection enabled for web forms.
- Use Laravel Breeze login throttling and secure password hashing.
- Regenerate sessions after login and privilege changes.
- Never hardcode credentials, API keys, application keys, database passwords, or SMS provider secrets.
- Keep medical files in private storage, not public web paths.
- Validate uploaded file type, size, extension, and ownership.
- Use private file download controllers with policy checks.
- Audit sensitive access and mutations.
- Wrap multi-table workflows in database transactions.
- Use guarded/fillable mass-assignment protection.
- Apply API rate limits when Sanctum APIs are added.
- Store only secure random QR tokens; never encode patient medical data in QR codes.
- Do not expose private medical files directly.
- Do not display raw exception messages in production.
- Preserve medical record amendment history.
- Preserve financial adjustment and refund history.
- Apply least privilege for every role.

## Audit Actions

Audit logging is required for login, logout, failed login, user creation, role assignment, patient record access, patient updates, medical record access, consultation finalization, laboratory result release, prescription creation, medicine dispensing, inventory adjustment, bill finalization, payment recording, report export, and system setting changes.

## Audit Log Columns

```text
id
user_id
action
module
auditable_type
auditable_id
description
old_values
new_values
ip_address
user_agent
created_at
```

Audit logs must be append-only from application routes. Ordinary users must not edit or delete audit logs.

## Record-Level Authorization Examples

- Patients can view only their own records.
- Doctors can view patients assigned through appointments or consultations.
- Nurses can access only information needed for triage and queue management.
- Pharmacists can view prescription and dispensing context, not full consultation notes.
- Cashiers can view billing and payment context, not detailed medical records.
- Laboratory staff can view only the clinical context needed to process laboratory tests.

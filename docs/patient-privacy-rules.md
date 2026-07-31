# Patient Privacy Rules

This project is a portfolio application and is not certified for production healthcare use. Real deployments require legal, privacy, infrastructure, retention, and security review.

## Data Minimization

QR codes contain only a signed lookup URL with a secure random token. They do not embed patient names, birth dates, contact numbers, diagnoses, allergies, documents, or other protected health information.

## Least Privilege

The canonical permission source is `app/Support/AccessControl.php`.

- Super Admin has all permissions.
- Hospital Admin manages administrative patient records.
- Nurse can support registration and basic patient updates.
- Doctor can view patient identity and manage allergy or condition facts needed for care.
- Pharmacist and Laboratory Staff can perform QR lookup and view patient identity needed for future workflows.
- Cashier can view basic patient identity only.
- Patient can view and update their own non-clinical profile and view their own QR card.

## Staff Views

Staff patient views are audited. Sensitive child sections are rendered only when the user has the relevant permission:

- Emergency contacts: `patients.manage-emergency-contacts`.
- Allergies: `patients.manage-allergies`.
- Conditions: `patients.manage-conditions`.
- Documents: `patients.manage-documents` or `patients.download-documents`.

## Archive Instead of Delete

Patient profiles use soft deletes. Archived records are hidden from normal operations but can be restored by authorized administrators. Hard deletion is intentionally not exposed.

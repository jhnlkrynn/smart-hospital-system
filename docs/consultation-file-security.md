# Consultation File Security

Consultation attachments are stored on the private `local` disk under `consultation-attachments/{consultation_id}`. Files are downloaded through authorized controller actions instead of public storage URLs.

## Rules

- Accepted files: PDF, JPG, PNG, DOC, and DOCX.
- Maximum upload size: 5 MB.
- Attachments can be marked confidential.
- Patient visibility is opt-in.
- Audit logs record upload events without storing clinical document contents.

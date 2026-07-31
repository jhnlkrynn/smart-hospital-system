# Electronic Medical Records

The EMR timeline is derived from finalized consultations. It is intentionally conservative: patients can view only their own completed consultations where `is_patient_visible` is true.

## Patient Access

Patient medical records are available at `/patient/medical-records`. The timeline includes visit date, department, doctor, patient summary, patient-visible diagnoses, treatment plan, follow-up instructions, and issued medical certificates.

## Staff Access

Doctors can view assigned consultation records. Hospital admins and super admins can view consultation metadata and summaries for operations, quality review, and reopening control, but clinical note editing remains doctor-owned.

## Privacy

Internal doctor notes, confidential attachments, drafts, reopened records, and other patients' records are not exposed through the patient portal.

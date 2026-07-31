# Consultation Management

Phase 8 connects the doctor queue to the clinical encounter record. Doctors start consultations from their assigned queue entries, record SOAP-style notes, add diagnoses, create attachments, and complete the encounter.

## Workflow

1. A nurse or receptionist checks in a patient and the queue reaches `triaged`.
2. The assigned doctor starts the consultation from `/doctor/queues`.
3. The system creates a `consultations` record with a `CON-{year}-{sequence}` number.
4. Starting the consultation moves the queue to `with_doctor` and the appointment to `in_progress`.
5. The doctor records clinical notes and at least one diagnosis.
6. Completing the consultation moves it to `completed`, marks the patient summary visible, closes the queue, and completes the appointment.

Completed consultations are read-only until an authorized administrator reopens them with a recorded reason.

## Clinical Fields

Consultations support subjective notes, objective notes, assessment, clinical impression, treatment plan, follow-up instructions, follow-up date, patient summary, and internal doctor notes. Patient-facing views never show internal doctor notes.

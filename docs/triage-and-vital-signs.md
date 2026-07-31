# Triage and Vital Signs

Phase 7 adds nurse triage and vital-sign recording before consultations are implemented.

## Triage Record

Triage records include:

- Queue.
- Appointment when present.
- Patient.
- Nurse.
- Chief complaint.
- Pain scale from 0 to 10.
- Pregnancy flag.
- Fall-risk score and level.
- Acuity.
- Allergy review flag.
- Notes.
- Start and completion timestamps.

Fall-risk level is derived from the score:

- 0 to 2: low.
- 3 to 5: moderate.
- 6 to 10: high.

## Vital Signs

Vital signs include:

- Blood pressure systolic and diastolic.
- Pulse rate.
- Respiratory rate.
- Temperature in Celsius.
- Oxygen saturation.
- Height.
- Weight.
- BMI.

BMI is calculated by `VitalSignsService` from height and weight:

```text
weight_kg / (height_m * height_m)
```

Vital signs are linked to the queue, patient, recorder, and triage record when available.

## Allergy Warning

Queue and triage screens display active patient allergy warnings so nurses and doctors see risk signals before the consultation phase.

# Consultation Status Workflow

Consultations use these statuses:

- `waiting`: record exists but the encounter has not started.
- `in_progress`: doctor is actively handling the encounter.
- `paused`: reserved for temporary interruptions.
- `completed`: finalized and read-only.
- `cancelled`: stopped before completion.
- `reopened`: previously completed and reopened for correction.

Editable statuses are `waiting`, `in_progress`, `paused`, and `reopened`. Completed and cancelled records are protected from direct clinical edits.

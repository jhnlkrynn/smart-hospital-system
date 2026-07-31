# Laboratory Request Workflow

Doctors create laboratory requests from assigned consultations. The patient, department, appointment, and requesting doctor are derived server-side from the consultation and authenticated user.

Request numbers use `LAB-YYYYMMDD-0001`. A request can contain multiple test items. Each item keeps snapshots of the test code, name, result type, unit, specimen type, priority, and status.

Requests move through specimen pending, collected, in process, completed, verified, released, cancelled, rejected, or recollection-required states.

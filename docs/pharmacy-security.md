# Pharmacy Security

Phase 10 uses role and permission middleware for all pharmacy routes.

Doctors can create and finalize prescriptions only for their assigned consultations. Patients can only view their own prescriptions. Pharmacists can review prescriptions, reserve stock, manage inventory batches, and receive purchases, but they cannot create prescriptions.

Clinical and pharmacy mutations write audit logs through the shared audit service.

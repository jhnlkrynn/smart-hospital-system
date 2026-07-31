# Stock Reservations

Pharmacists can reserve stock for finalized or reviewed prescriptions. The reservation workflow uses FEFO ordering by expiration date and skips expired or quarantined batches.

Reservations create rows in `pharmacy_stock_reservations` and ledger entries of type `reservation`. Releasing a reservation decreases the batch reserved quantity and records a `reservation_release` ledger entry.

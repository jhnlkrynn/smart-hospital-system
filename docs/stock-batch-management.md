# Stock Batch Management

Medication stock batches are the inventory source of truth.

Reservations increase `quantity_reserved`; they do not reduce `quantity_on_hand`. Available quantity is derived as on hand minus reserved. Expired and quarantined batches are excluded from reservation.

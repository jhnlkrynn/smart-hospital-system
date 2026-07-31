@include('dashboards.partials.role-dashboard', [
    'title' => 'Pharmacist Dashboard',
    'subtitle' => 'Pharmacy workspace for prescriptions, medicines, batches, suppliers, inventory, and dispensing.',
    'primary' => [
        'title' => 'Prescription Review and Inventory',
        'description' => 'Pharmacists can review finalized prescriptions, reserve stock, receive purchases, adjust batches, and quarantine inventory before Phase 11 dispensing.',
    ],
    'cards' => [
        ['title' => 'Prescription Review', 'description' => 'Open finalized prescriptions for pharmacy review and stock reservation.'],
        ['title' => 'Medication Catalog', 'description' => 'Maintain formulary medications, dosage forms, routes, units, and safety flags.'],
        ['title' => 'Inventory Batches', 'description' => 'Track on-hand and reserved quantities by batch, location, lot, and expiry.'],
        ['title' => 'Purchase Receiving', 'description' => 'Receive supplier purchase orders directly into stock batches.'],
        ['title' => 'Stock Controls', 'description' => 'Record adjustments, quarantine batches, and review the inventory transaction ledger.'],
    ],
])

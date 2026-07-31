@include('dashboards.partials.role-dashboard', [
    'title' => 'Pharmacist Dashboard',
    'subtitle' => 'Pharmacy workspace for prescriptions, medicines, batches, suppliers, inventory, and dispensing.',
    'primary' => [
        'title' => 'Dispensing and Inventory Access',
        'description' => 'Pharmacists can work with prescription dispensing and inventory, without complete consultation-note access.',
    ],
    'cards' => [
        ['title' => 'Prescriptions to Dispense', 'description' => 'Upcoming active prescription queue.'],
        ['title' => 'Medicines', 'description' => 'Upcoming medicine catalog.'],
        ['title' => 'Inventory', 'description' => 'Upcoming stock ledger and batch controls.'],
        ['title' => 'Low Stock', 'description' => 'Upcoming low-stock monitoring.'],
        ['title' => 'Expiration Monitoring', 'description' => 'Upcoming expiring batch alerts.'],
    ],
])

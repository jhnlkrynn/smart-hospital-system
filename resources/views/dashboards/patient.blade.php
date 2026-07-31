@include('dashboards.partials.role-dashboard', [
    'title' => 'Patient Dashboard',
    'subtitle' => 'Personal health portal for own profile, appointments, released results, prescriptions, bills, and symptom checker history.',
    'primary' => [
        'title' => 'Own-Record Access',
        'description' => 'Patient permissions are limited to the signed-in user account and its own future patient record.',
    ],
    'cards' => [
        ['title' => 'My Profile', 'description' => 'Upcoming patient profile tools.'],
        ['title' => 'Book Appointment', 'description' => 'Upcoming appointment booking.'],
        ['title' => 'Queue Status', 'description' => 'Upcoming own queue status view.'],
        ['title' => 'Laboratory Results', 'description' => 'Upcoming released results view.'],
        ['title' => 'Prescriptions', 'description' => 'Upcoming prescription history.'],
        ['title' => 'Bills', 'description' => 'Upcoming billing view.'],
        ['title' => 'Digital QR ID', 'description' => 'Upcoming secure patient QR identifier.'],
        ['title' => 'Symptom Checker', 'description' => 'Upcoming rule-based symptom checker.'],
    ],
])

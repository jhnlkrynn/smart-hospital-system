@include('dashboards.partials.role-dashboard', [
    'title' => 'Doctor Dashboard',
    'subtitle' => 'Clinical workspace for assigned appointments, consultations, lab requests, and prescriptions once patient-care modules are available.',
    'primary' => [
        'title' => 'Assigned-Record Access',
        'description' => 'Doctor permissions are scoped to assigned patients and clinical work. Inventory, payments, role management, and settings remain restricted.',
    ],
    'cards' => [
        ['title' => 'My Schedule', 'description' => 'Upcoming doctor schedule view.'],
        ['title' => 'Assigned Appointments', 'description' => 'Upcoming appointment list limited to assigned records.'],
        ['title' => 'Consultations', 'description' => 'Upcoming consultation workflow.'],
        ['title' => 'Laboratory Results', 'description' => 'Upcoming released results access.'],
        ['title' => 'Prescriptions', 'description' => 'Upcoming prescription creation and review.'],
    ],
])

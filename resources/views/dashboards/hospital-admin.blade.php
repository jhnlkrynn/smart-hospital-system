@include('dashboards.partials.role-dashboard', [
    'title' => 'Hospital Admin Dashboard',
    'subtitle' => 'Administrative workspace for managing hospital structure once the upcoming modules are built.',
    'primary' => [
        'title' => 'Administrative Scope',
        'description' => 'Hospital Admin access is limited to operational administration and excludes unrestricted role management and sensitive audit logs.',
    ],
    'cards' => [
        ['title' => 'Department Management', 'description' => 'Upcoming module for hospital departments.'],
        ['title' => 'Employee Management', 'description' => 'Upcoming module for staff records and account coordination.'],
        ['title' => 'Patient Administration', 'description' => 'Upcoming module for patient registration support.'],
        ['title' => 'Appointment Administration', 'description' => 'Upcoming module for schedule and appointment coordination.'],
        ['title' => 'Reports', 'description' => 'Upcoming administrative reports.'],
        ['title' => 'Announcements', 'description' => 'Upcoming hospital announcement publishing.'],
    ],
])

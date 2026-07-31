@include('dashboards.partials.role-dashboard', [
    'title' => 'Nurse Dashboard',
    'subtitle' => 'Patient intake workspace for registration support, check-in, queues, triage, and vital signs.',
    'primary' => [
        'title' => 'Care Intake Access',
        'description' => 'Nurse permissions focus on front-line intake and triage, without billing administration or unrestricted consultation notes.',
    ],
    'cards' => [
        ['title' => 'Patient Registration', 'description' => 'Upcoming patient intake workflow.'],
        ['title' => 'Appointment Check-In', 'description' => 'Upcoming check-in controls.'],
        ['title' => 'Queue Management', 'description' => 'Upcoming queue calling and transfer tools.'],
        ['title' => 'Triage', 'description' => 'Upcoming triage assessment form.'],
        ['title' => 'Vital Signs', 'description' => 'Upcoming vital-sign recording.'],
    ],
])

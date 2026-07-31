@include('dashboards.partials.role-dashboard', [
    'title' => 'Laboratory Dashboard',
    'subtitle' => 'Laboratory workspace for test definitions, requests, specimen collection, result entry, and release.',
    'primary' => [
        'title' => 'Laboratory-Only Scope',
        'description' => 'Laboratory Staff access is limited to lab processing context and excludes unrelated medical and financial records.',
    ],
    'cards' => [
        ['title' => 'New Laboratory Requests', 'description' => 'Upcoming request queue.'],
        ['title' => 'Specimen Collection', 'description' => 'Upcoming collection tracking.'],
        ['title' => 'Processing Tests', 'description' => 'Upcoming processing workflow.'],
        ['title' => 'Results for Release', 'description' => 'Upcoming result release controls.'],
    ],
])

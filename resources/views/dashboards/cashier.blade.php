@include('dashboards.partials.role-dashboard', [
    'title' => 'Cashier Dashboard',
    'subtitle' => 'Financial workspace for bills, payments, receipts, and collection reports.',
    'primary' => [
        'title' => 'Financial Access Only',
        'description' => 'Cashier permissions cover billing and payments, while detailed medical records and consultation notes remain restricted.',
    ],
    'cards' => [
        ['title' => 'Pending Bills', 'description' => 'Upcoming billing queue.'],
        ['title' => 'Record Payment', 'description' => 'Upcoming payment recording.'],
        ['title' => 'Payment History', 'description' => 'Upcoming payment ledger.'],
        ['title' => 'Daily Collection Report', 'description' => 'Upcoming collection summary.'],
    ],
])

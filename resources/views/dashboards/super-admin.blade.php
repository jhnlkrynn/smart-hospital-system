@include('dashboards.partials.role-dashboard', [
    'title' => 'Super Admin Dashboard',
    'subtitle' => 'Access-control oversight for roles, permissions, settings, and audit readiness.',
    'primary' => [
        'title' => 'System Access Foundation',
        'description' => 'Roles, permissions, protected dashboards, account status checks, and development demo accounts are available in this phase.',
    ],
    'cards' => [
        ['title' => 'User and Access Control', 'description' => 'Manage users and access assignments in the upcoming administration UI.'],
        ['title' => 'Roles and Permissions', 'description' => 'Review the seeded role and permission model from the Phase 3 matrix.'],
        ['title' => 'System Settings', 'description' => 'Configuration screens are planned for a later phase.'],
        ['title' => 'Audit Logs', 'description' => 'Immutable audit tracking is planned before final release.'],
        ['title' => 'Development Progress', 'description' => 'Phase 3 completes the access-control foundation.'],
    ],
])

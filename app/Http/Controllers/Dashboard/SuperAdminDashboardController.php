<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    use DashboardData;

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.super-admin'), 403);

        return view('dashboards.super-admin', $this->dashboardData($request->user(), 'Super Admin', 'super-admin'));
    }
}

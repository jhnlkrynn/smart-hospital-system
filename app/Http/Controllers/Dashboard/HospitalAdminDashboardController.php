<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalAdminDashboardController extends Controller
{
    use DashboardData;

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.hospital-admin'), 403);

        return view('dashboards.hospital-admin', $this->dashboardData($request->user(), 'Hospital Admin', 'hospital-admin'));
    }
}

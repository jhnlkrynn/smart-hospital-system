<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorDashboardController extends Controller
{
    use DashboardData;

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.doctor'), 403);

        return view('dashboards.doctor', $this->dashboardData($request->user(), 'Doctor', 'doctor'));
    }
}

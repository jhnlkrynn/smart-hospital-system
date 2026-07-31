<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NurseDashboardController extends Controller
{
    use DashboardData;

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.nurse'), 403);

        return view('dashboards.nurse', $this->dashboardData($request->user(), 'Nurse', 'nurse'));
    }
}

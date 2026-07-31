<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaboratoryDashboardController extends Controller
{
    use DashboardData;

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.laboratory'), 403);

        return view('dashboards.laboratory', $this->dashboardData($request->user(), 'Laboratory Staff', 'laboratory-staff'));
    }
}

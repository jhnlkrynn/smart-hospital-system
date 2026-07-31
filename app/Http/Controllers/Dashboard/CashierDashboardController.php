<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashierDashboardController extends Controller
{
    use DashboardData;

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('dashboard.cashier'), 403);

        return view('dashboards.cashier', $this->dashboardData($request->user(), 'Cashier', 'cashier'));
    }
}

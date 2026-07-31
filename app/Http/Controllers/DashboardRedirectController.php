<?php

namespace App\Http\Controllers;

use App\Services\Auth\RoleRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request, RoleRedirectService $redirects): RedirectResponse|View
    {
        if (! $request->user()->roles()->exists()) {
            return view('dashboards.account-pending');
        }

        return redirect($redirects->redirectPathFor($request->user()));
    }
}

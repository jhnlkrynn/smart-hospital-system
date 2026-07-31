<?php

use App\Http\Controllers\Dashboard\CashierDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:cashier', 'permission:dashboard.cashier'])
    ->prefix('cashier')
    ->name('cashier.')
    ->group(function (): void {
        Route::get('/dashboard', CashierDashboardController::class)->name('dashboard');
    });

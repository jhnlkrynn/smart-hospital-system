<?php

use App\Http\Controllers\Dashboard\PharmacistDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:pharmacist', 'permission:dashboard.pharmacist'])
    ->prefix('pharmacist')
    ->name('pharmacist.')
    ->group(function (): void {
        Route::get('/dashboard', PharmacistDashboardController::class)->name('dashboard');
    });

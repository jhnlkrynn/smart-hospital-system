<?php

use App\Http\Controllers\Dashboard\LaboratoryDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:laboratory-staff', 'permission:dashboard.laboratory'])
    ->prefix('laboratory')
    ->name('laboratory.')
    ->group(function (): void {
        Route::get('/dashboard', LaboratoryDashboardController::class)->name('dashboard');
    });

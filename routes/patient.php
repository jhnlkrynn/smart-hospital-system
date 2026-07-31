<?php

use App\Http\Controllers\Dashboard\PatientDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:patient', 'permission:dashboard.patient'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function (): void {
        Route::get('/dashboard', PatientDashboardController::class)->name('dashboard');
    });

<?php

use App\Http\Controllers\Dashboard\DoctorDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:doctor', 'permission:dashboard.doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function (): void {
        Route::get('/dashboard', DoctorDashboardController::class)->name('dashboard');
    });

<?php

use App\Http\Controllers\Dashboard\HospitalAdminDashboardController;
use App\Http\Controllers\Dashboard\SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active'])->group(function (): void {
    Route::prefix('super-admin')
        ->name('super-admin.')
        ->middleware(['role:super-admin', 'permission:dashboard.super-admin'])
        ->group(function (): void {
            Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');
        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:hospital-admin', 'permission:dashboard.hospital-admin'])
        ->group(function (): void {
            Route::get('/dashboard', HospitalAdminDashboardController::class)->name('dashboard');
        });
});

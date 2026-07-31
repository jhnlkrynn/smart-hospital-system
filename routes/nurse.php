<?php

use App\Http\Controllers\Dashboard\NurseDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:nurse', 'permission:dashboard.nurse'])
    ->prefix('nurse')
    ->name('nurse.')
    ->group(function (): void {
        Route::get('/dashboard', NurseDashboardController::class)->name('dashboard');
    });

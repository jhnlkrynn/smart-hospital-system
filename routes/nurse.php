<?php

use App\Http\Controllers\Dashboard\NurseDashboardController;
use App\Http\Controllers\Nurse\TriageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:nurse', 'permission:dashboard.nurse'])
    ->prefix('nurse')
    ->name('nurse.')
    ->group(function (): void {
        Route::get('/dashboard', NurseDashboardController::class)->name('dashboard');
        Route::get('/triage', [TriageController::class, 'index'])
            ->middleware('permission:triage.view')
            ->name('triage.index');
        Route::get('/triage/queues/{queue}', [TriageController::class, 'create'])
            ->middleware('permission:triage.create')
            ->name('triage.create');
        Route::post('/triage/queues/{queue}', [TriageController::class, 'store'])
            ->middleware('permission:triage.create')
            ->name('triage.store');
        Route::post('/triage/queues/{queue}/vitals', [TriageController::class, 'storeVitals'])
            ->middleware('permission:vital-signs.create')
            ->name('triage.vitals.store');
    });

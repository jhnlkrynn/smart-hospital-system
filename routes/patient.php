<?php

use App\Http\Controllers\Dashboard\PatientDashboardController;
use App\Http\Controllers\Patient\PatientProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:patient', 'permission:dashboard.patient'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function (): void {
        Route::get('/dashboard', PatientDashboardController::class)->name('dashboard');
        Route::get('/profile', [PatientProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [PatientProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [PatientProfileController::class, 'update'])->name('profile.update');
        Route::get('/qr-card', [PatientProfileController::class, 'qrCard'])->name('qr-card');
        Route::get('/qr-card/download', [PatientProfileController::class, 'qrCard'])->name('qr-card.download');
    });

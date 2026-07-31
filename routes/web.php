<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientLookup\PatientLookupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified', 'account.active'])
    ->name('dashboard');

Route::view('/account/pending', 'dashboards.account-pending')
    ->middleware(['auth', 'verified', 'account.active'])
    ->name('account.pending');

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/employment', [EmployeeController::class, 'ownProfile'])
        ->middleware('role:super-admin|hospital-admin|doctor|nurse|pharmacist|laboratory-staff|cashier')
        ->name('profile.employment');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified', 'account.active', 'permission:patients.lookup-qr', 'throttle:30,1'])
    ->prefix('patient-lookup')
    ->name('patient-lookup.')
    ->group(function (): void {
        Route::get('/', [PatientLookupController::class, 'index'])->name('index');
        Route::post('/', [PatientLookupController::class, 'store'])->name('store');
        Route::get('/{token}', [PatientLookupController::class, 'show'])->name('show');
    });

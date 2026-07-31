<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\ProfileController;
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

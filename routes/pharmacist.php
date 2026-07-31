<?php

use App\Http\Controllers\Dashboard\PharmacistDashboardController;
use App\Http\Controllers\Pharmacist\InventoryController;
use App\Http\Controllers\Pharmacist\PharmacyPrescriptionController;
use App\Http\Controllers\Pharmacist\PurchaseController;
use App\Http\Controllers\Pharmacist\ReservationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:pharmacist', 'permission:dashboard.pharmacist'])
    ->prefix('pharmacist')
    ->name('pharmacist.')
    ->group(function (): void {
        Route::get('/dashboard', PharmacistDashboardController::class)->name('dashboard');
        Route::get('/prescriptions', [PharmacyPrescriptionController::class, 'index'])
            ->middleware('permission:prescriptions.view-all')
            ->name('prescriptions.index');
        Route::get('/prescriptions/{prescription}', [PharmacyPrescriptionController::class, 'show'])
            ->middleware('permission:prescriptions.view-all')
            ->name('prescriptions.show');
        Route::post('/prescriptions/{prescription}/review', [PharmacyPrescriptionController::class, 'review'])
            ->middleware('permission:prescriptions.review')
            ->name('prescriptions.review');
        Route::post('/prescriptions/{prescription}/reserve', [PharmacyPrescriptionController::class, 'reserve'])
            ->middleware('permission:prescriptions.reserve-stock')
            ->name('prescriptions.reserve');

        Route::get('/inventory', [InventoryController::class, 'index'])
            ->middleware('permission:pharmacy-inventory.view')
            ->name('inventory.index');
        Route::post('/inventory/batches/{batch}/adjust', [InventoryController::class, 'adjust'])
            ->middleware('permission:pharmacy-inventory.adjust')
            ->name('inventory.adjust');
        Route::post('/inventory/batches/{batch}/quarantine', [InventoryController::class, 'quarantine'])
            ->middleware('permission:pharmacy-inventory.quarantine')
            ->name('inventory.quarantine');
        Route::post('/inventory/batches/{batch}/unquarantine', [InventoryController::class, 'unquarantine'])
            ->middleware('permission:pharmacy-inventory.unquarantine')
            ->name('inventory.unquarantine');
        Route::post('/reservations/{reservation}/release', [ReservationController::class, 'release'])
            ->middleware('permission:pharmacy-inventory.release-reservation')
            ->name('reservations.release');

        Route::get('/purchases', [PurchaseController::class, 'index'])
            ->middleware('permission:pharmacy-purchases.view')
            ->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])
            ->middleware('permission:pharmacy-purchases.create')
            ->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])
            ->middleware('permission:pharmacy-purchases.create')
            ->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])
            ->middleware('permission:pharmacy-purchases.view')
            ->name('purchases.show');
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])
            ->middleware('permission:pharmacy-purchases.receive')
            ->name('purchases.receive');
    });

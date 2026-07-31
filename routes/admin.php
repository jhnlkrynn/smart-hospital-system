<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PatientController;
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

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:super-admin|hospital-admin'])
        ->group(function (): void {
            Route::resource('departments', DepartmentController::class)
                ->middleware([
                    'index' => 'permission:departments.view',
                    'show' => 'permission:departments.view',
                    'create' => 'permission:departments.create',
                    'store' => 'permission:departments.create',
                    'edit' => 'permission:departments.update',
                    'update' => 'permission:departments.update',
                    'destroy' => 'permission:departments.archive',
                ]);
            Route::patch('departments/{department}/restore', [DepartmentController::class, 'restore'])
                ->middleware('permission:departments.restore')
                ->name('departments.restore');

            Route::resource('employees', EmployeeController::class)
                ->middleware([
                    'index' => 'permission:employees.view',
                    'show' => 'permission:employees.view',
                    'create' => 'permission:employees.create',
                    'store' => 'permission:employees.create',
                    'edit' => 'permission:employees.update',
                    'update' => 'permission:employees.update',
                    'destroy' => 'permission:employees.archive',
                ]);
            Route::patch('employees/{employee}/restore', [EmployeeController::class, 'restore'])
                ->middleware('permission:employees.restore')
                ->name('employees.restore');

        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:super-admin|hospital-admin|doctor|nurse|pharmacist|laboratory-staff|cashier'])
        ->group(function (): void {
            Route::get('patients', [PatientController::class, 'index'])
                ->middleware('permission:patients.view')
                ->name('patients.index');
            Route::get('patients/create', [PatientController::class, 'create'])
                ->middleware('permission:patients.create')
                ->name('patients.create');
            Route::post('patients', [PatientController::class, 'store'])
                ->middleware('permission:patients.create')
                ->name('patients.store');
            Route::get('patients/{patient}', [PatientController::class, 'show'])
                ->middleware('permission:patients.view')
                ->name('patients.show');
            Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])
                ->middleware('permission:patients.update')
                ->name('patients.edit');
            Route::match(['put', 'patch'], 'patients/{patient}', [PatientController::class, 'update'])
                ->middleware('permission:patients.update')
                ->name('patients.update');
            Route::delete('patients/{patient}', [PatientController::class, 'destroy'])
                ->middleware('permission:patients.archive')
                ->name('patients.destroy');
            Route::patch('patients/{patient}/restore', [PatientController::class, 'restore'])
                ->middleware('permission:patients.restore')
                ->name('patients.restore');
            Route::post('patients/{patient}/regenerate-qr', [PatientController::class, 'regenerateQr'])
                ->middleware('permission:patients.view-qr')
                ->name('patients.regenerate-qr');
            Route::post('patients/{patient}/emergency-contacts', [PatientController::class, 'storeEmergencyContact'])
                ->middleware('permission:patients.manage-emergency-contacts')
                ->name('patients.emergency-contacts.store');
            Route::post('patients/{patient}/allergies', [PatientController::class, 'storeAllergy'])
                ->middleware('permission:patients.manage-allergies')
                ->name('patients.allergies.store');
            Route::post('patients/{patient}/conditions', [PatientController::class, 'storeCondition'])
                ->middleware('permission:patients.manage-conditions')
                ->name('patients.conditions.store');
            Route::post('patients/{patient}/documents', [PatientController::class, 'storeDocument'])
                ->middleware('permission:patients.manage-documents')
                ->name('patients.documents.store');
            Route::get('patients/{patient}/documents/{document}/download', [PatientController::class, 'downloadDocument'])
                ->middleware('permission:patients.download-documents')
                ->name('patients.documents.download');
        });
});

<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
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
});

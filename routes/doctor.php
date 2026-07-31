<?php

use App\Http\Controllers\Dashboard\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\DoctorQueueController;
use App\Http\Controllers\Doctor\DoctorScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:doctor', 'permission:dashboard.doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function (): void {
        Route::get('/dashboard', DoctorDashboardController::class)->name('dashboard');
        Route::get('/schedule', [DoctorScheduleController::class, 'index'])
            ->middleware('permission:doctor-schedules.view')
            ->name('schedule.index');
        Route::get('/appointments', [DoctorAppointmentController::class, 'index'])
            ->middleware('permission:appointments.view-assigned')
            ->name('appointments.index');
        Route::get('/appointments/{appointment}', [DoctorAppointmentController::class, 'show'])
            ->middleware('permission:appointments.view-assigned')
            ->name('appointments.show');
        Route::post('/appointments/{appointment}/approve', [DoctorAppointmentController::class, 'approve'])
            ->middleware('permission:appointments.approve')
            ->name('appointments.approve');
        Route::post('/appointments/{appointment}/reject', [DoctorAppointmentController::class, 'reject'])
            ->middleware('permission:appointments.reject')
            ->name('appointments.reject');
        Route::post('/appointments/{appointment}/complete', [DoctorAppointmentController::class, 'complete'])
            ->middleware('permission:appointments.complete')
            ->name('appointments.complete');
        Route::get('/queues', [DoctorQueueController::class, 'index'])
            ->middleware('permission:queues.view')
            ->name('queues.index');
        Route::post('/queues/{queue}/start', [DoctorQueueController::class, 'start'])
            ->middleware('permission:queues.complete')
            ->name('queues.start');
        Route::post('/queues/{queue}/complete', [DoctorQueueController::class, 'complete'])
            ->middleware('permission:queues.complete')
            ->name('queues.complete');
    });

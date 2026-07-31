<?php

use App\Http\Controllers\Dashboard\PatientDashboardController;
use App\Http\Controllers\Patient\MedicalRecordController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientLaboratoryResultController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\Patient\PatientQueueController;
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
        Route::get('/appointments/slots', [PatientAppointmentController::class, 'slots'])
            ->middleware('permission:appointments.create')
            ->name('appointments.slots');
        Route::get('/appointments', [PatientAppointmentController::class, 'index'])
            ->middleware('permission:appointments.view-own')
            ->name('appointments.index');
        Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])
            ->middleware('permission:appointments.create')
            ->name('appointments.create');
        Route::post('/appointments', [PatientAppointmentController::class, 'store'])
            ->middleware('permission:appointments.create')
            ->name('appointments.store');
        Route::get('/appointments/{appointment}', [PatientAppointmentController::class, 'show'])
            ->middleware('permission:appointments.view-own')
            ->name('appointments.show');
        Route::post('/appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel'])
            ->middleware('permission:appointments.cancel')
            ->name('appointments.cancel');
        Route::post('/appointments/{appointment}/reschedule', [PatientAppointmentController::class, 'reschedule'])
            ->middleware('permission:appointments.reschedule')
            ->name('appointments.reschedule');
        Route::get('/queues', [PatientQueueController::class, 'index'])
            ->middleware('permission:queues.view-own-status')
            ->name('queues.index');
        Route::get('/medical-records', [MedicalRecordController::class, 'index'])
            ->middleware('permission:medical-records.view-own')
            ->name('medical-records.index');
        Route::get('/medical-records/{consultation}', [MedicalRecordController::class, 'show'])
            ->middleware('permission:medical-records.view-own')
            ->name('medical-records.show');
        Route::get('/laboratory-results', [PatientLaboratoryResultController::class, 'index'])
            ->middleware('permission:laboratory-results.view-own')
            ->name('laboratory-results.index');
        Route::get('/laboratory-results/{result}', [PatientLaboratoryResultController::class, 'show'])
            ->middleware('permission:laboratory-results.view-own')
            ->name('laboratory-results.show');
        Route::get('/laboratory-results/{result}/download', [PatientLaboratoryResultController::class, 'download'])
            ->middleware('permission:laboratory-results.download')
            ->name('laboratory-results.download');
    });

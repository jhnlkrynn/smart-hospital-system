<?php

use App\Http\Controllers\Dashboard\DoctorDashboardController;
use App\Http\Controllers\Doctor\ConsultationAttachmentController;
use App\Http\Controllers\Doctor\ConsultationController;
use App\Http\Controllers\Doctor\ConsultationDiagnosisController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\DoctorQueueController;
use App\Http\Controllers\Doctor\DoctorScheduleController;
use App\Http\Controllers\Doctor\MedicalCertificateController;
use App\Http\Controllers\Doctor\LaboratoryRequestController;
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
        Route::post('/queues/{queue}/consultations/start', [ConsultationController::class, 'start'])
            ->middleware('permission:consultations.start')
            ->name('queues.consultations.start');
        Route::post('/queues/{queue}/complete', [DoctorQueueController::class, 'complete'])
            ->middleware('permission:queues.complete')
            ->name('queues.complete');

        Route::get('/consultations', [ConsultationController::class, 'index'])
            ->middleware('permission:consultations.view-assigned')
            ->name('consultations.index');
        Route::get('/consultations/{consultation}', [ConsultationController::class, 'show'])
            ->middleware('permission:consultations.view-assigned')
            ->name('consultations.show');
        Route::put('/consultations/{consultation}', [ConsultationController::class, 'update'])
            ->middleware('permission:consultations.update')
            ->name('consultations.update');
        Route::post('/consultations/{consultation}/complete', [ConsultationController::class, 'complete'])
            ->middleware('permission:consultations.complete')
            ->name('consultations.complete');
        Route::post('/consultations/{consultation}/cancel', [ConsultationController::class, 'cancel'])
            ->middleware('permission:consultations.cancel')
            ->name('consultations.cancel');
        Route::post('/consultations/{consultation}/diagnoses', [ConsultationDiagnosisController::class, 'store'])
            ->middleware('permission:diagnoses.create')
            ->name('consultations.diagnoses.store');
        Route::post('/consultations/{consultation}/attachments', [ConsultationAttachmentController::class, 'store'])
            ->middleware('permission:clinical-attachments.upload')
            ->name('consultations.attachments.store');
        Route::get('/consultations/{consultation}/attachments/{attachment}/download', [ConsultationAttachmentController::class, 'download'])
            ->middleware('permission:clinical-attachments.download')
            ->name('consultations.attachments.download');
        Route::post('/consultations/{consultation}/medical-certificates', [MedicalCertificateController::class, 'store'])
            ->middleware('permission:medical-certificates.create')
            ->name('consultations.certificates.store');
        Route::get('/consultations/{consultation}/medical-certificates/{certificate}', [MedicalCertificateController::class, 'show'])
            ->middleware('permission:medical-certificates.view')
            ->name('consultations.certificates.show');
        Route::put('/consultations/{consultation}/medical-certificates/{certificate}', [MedicalCertificateController::class, 'update'])
            ->middleware('permission:medical-certificates.update')
            ->name('consultations.certificates.update');
        Route::post('/consultations/{consultation}/medical-certificates/{certificate}/issue', [MedicalCertificateController::class, 'issue'])
            ->middleware('permission:medical-certificates.issue')
            ->name('consultations.certificates.issue');
        Route::post('/consultations/{consultation}/medical-certificates/{certificate}/void', [MedicalCertificateController::class, 'voidCertificate'])
            ->middleware('permission:medical-certificates.void')
            ->name('consultations.certificates.void');

        Route::get('/laboratory-requests', [LaboratoryRequestController::class, 'index'])
            ->middleware('permission:laboratory-requests.view-assigned')
            ->name('laboratory-requests.index');
        Route::get('/consultations/{consultation}/laboratory-requests/create', [LaboratoryRequestController::class, 'create'])
            ->middleware('permission:laboratory-requests.create')
            ->name('consultations.laboratory-requests.create');
        Route::post('/consultations/{consultation}/laboratory-requests', [LaboratoryRequestController::class, 'store'])
            ->middleware('permission:laboratory-requests.create')
            ->name('consultations.laboratory-requests.store');
        Route::get('/laboratory-requests/{laboratoryRequest}', [LaboratoryRequestController::class, 'show'])
            ->middleware('permission:laboratory-requests.view-assigned')
            ->name('laboratory-requests.show');
        Route::post('/laboratory-results/{result}/acknowledge', [LaboratoryRequestController::class, 'acknowledge'])
            ->middleware('permission:laboratory-results.acknowledge')
            ->name('laboratory-results.acknowledge');
    });

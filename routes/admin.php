<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\DiagnosisCatalogController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AppointmentTypeController;
use App\Http\Controllers\Admin\DoctorScheduleController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\QueueController;
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

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:super-admin|hospital-admin|nurse'])
        ->group(function (): void {
            Route::resource('appointment-types', AppointmentTypeController::class)
                ->parameters(['appointment-types' => 'appointmentType'])
                ->middleware('permission:appointments.manage-all');

            Route::resource('doctor-schedules', DoctorScheduleController::class)
                ->parameters(['doctor-schedules' => 'doctorSchedule'])
                ->middleware([
                    'index' => 'permission:doctor-schedules.view',
                    'show' => 'permission:doctor-schedules.view',
                    'create' => 'permission:doctor-schedules.create',
                    'store' => 'permission:doctor-schedules.create',
                    'edit' => 'permission:doctor-schedules.update',
                    'update' => 'permission:doctor-schedules.update',
                    'destroy' => 'permission:doctor-schedules.archive',
                ]);
            Route::get('doctor-schedule-exceptions', [DoctorScheduleController::class, 'exceptions'])
                ->middleware('permission:doctor-schedules.manage-exceptions')
                ->name('doctor-schedule-exceptions.index');
            Route::post('doctor-schedule-exceptions', [DoctorScheduleController::class, 'storeException'])
                ->middleware('permission:doctor-schedules.manage-exceptions')
                ->name('doctor-schedule-exceptions.store');

            Route::get('appointments/slots', [AppointmentController::class, 'slots'])
                ->middleware('permission:appointments.create')
                ->name('appointments.slots');
            Route::get('appointments', [AppointmentController::class, 'index'])
                ->middleware('permission:appointments.view')
                ->name('appointments.index');
            Route::get('appointments/create', [AppointmentController::class, 'create'])
                ->middleware('permission:appointments.create-for-patient')
                ->name('appointments.create');
            Route::post('appointments', [AppointmentController::class, 'store'])
                ->middleware('permission:appointments.create-for-patient')
                ->name('appointments.store');
            Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])
                ->middleware('permission:appointments.view')
                ->name('appointments.show');
            Route::post('appointments/{appointment}/approve', [AppointmentController::class, 'approve'])
                ->middleware('permission:appointments.approve')
                ->name('appointments.approve');
            Route::post('appointments/{appointment}/reject', [AppointmentController::class, 'reject'])
                ->middleware('permission:appointments.reject')
                ->name('appointments.reject');
            Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
                ->middleware('permission:appointments.cancel')
                ->name('appointments.cancel');
            Route::post('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])
                ->middleware('permission:appointments.reschedule')
                ->name('appointments.reschedule');
            Route::post('appointments/{appointment}/complete', [AppointmentController::class, 'complete'])
                ->middleware('permission:appointments.complete')
                ->name('appointments.complete');
            Route::post('appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow'])
                ->middleware('permission:appointments.mark-no-show')
                ->name('appointments.no-show');

            Route::get('queues', [QueueController::class, 'index'])
                ->middleware('permission:queues.view')
                ->name('queues.index');
            Route::get('queues/create', [QueueController::class, 'create'])
                ->middleware('permission:queues.manage')
                ->name('queues.create');
            Route::post('queues', [QueueController::class, 'store'])
                ->middleware('permission:queues.manage')
                ->name('queues.store');
            Route::get('queues/{queue}', [QueueController::class, 'show'])
                ->middleware('permission:queues.view')
                ->name('queues.show');
            Route::post('appointments/{appointment}/check-in', [QueueController::class, 'checkIn'])
                ->middleware('permission:appointments.check-in')
                ->name('appointments.check-in');
            Route::post('queues/departments/{department}/call-next', [QueueController::class, 'callNext'])
                ->middleware('permission:queues.call')
                ->name('queues.call-next');
            Route::post('queues/{queue}/doctor', [QueueController::class, 'startDoctor'])
                ->middleware('permission:queues.transfer')
                ->name('queues.doctor');
            Route::post('queues/{queue}/complete', [QueueController::class, 'complete'])
                ->middleware('permission:queues.complete')
                ->name('queues.complete');
            Route::post('queues/{queue}/skip', [QueueController::class, 'skip'])
                ->middleware('permission:queues.skip')
                ->name('queues.skip');
            Route::post('queues/{queue}/cancel', [QueueController::class, 'cancel'])
                ->middleware('permission:queues.manage')
                ->name('queues.cancel');

            Route::get('consultations', [AdminConsultationController::class, 'index'])
                ->middleware('permission:consultations.view-all')
                ->name('consultations.index');
            Route::get('consultations/{consultation}', [AdminConsultationController::class, 'show'])
                ->middleware('permission:consultations.view-all')
                ->name('consultations.show');
            Route::post('consultations/{consultation}/reopen', [AdminConsultationController::class, 'reopen'])
                ->middleware('permission:consultations.reopen')
                ->name('consultations.reopen');

            Route::resource('diagnosis-catalog', DiagnosisCatalogController::class)
                ->except(['show'])
                ->middleware('permission:diagnoses.manage-catalog');
        });
});

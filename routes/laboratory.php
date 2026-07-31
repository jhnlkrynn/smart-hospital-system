<?php

use App\Http\Controllers\Dashboard\LaboratoryDashboardController;
use App\Http\Controllers\Laboratory\LaboratoryWorkQueueController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'account.active', 'role:laboratory-staff', 'permission:dashboard.laboratory'])
    ->prefix('laboratory')
    ->name('laboratory.')
    ->group(function (): void {
        Route::get('/dashboard', LaboratoryDashboardController::class)->name('dashboard');
        Route::get('/requests', [LaboratoryWorkQueueController::class, 'index'])
            ->middleware('permission:laboratory-requests.view')
            ->name('requests.index');
        Route::get('/requests/{laboratoryRequest}', [LaboratoryWorkQueueController::class, 'show'])
            ->middleware('permission:laboratory-requests.view')
            ->name('requests.show');
        Route::post('/requests/{laboratoryRequest}/specimens', [LaboratoryWorkQueueController::class, 'collect'])
            ->middleware('permission:laboratory-requests.collect-specimen')
            ->name('requests.specimens.collect');
        Route::post('/specimens/{specimen}/accept', [LaboratoryWorkQueueController::class, 'accept'])
            ->middleware('permission:laboratory-requests.collect-specimen')
            ->name('specimens.accept');
        Route::post('/specimens/{specimen}/reject', [LaboratoryWorkQueueController::class, 'reject'])
            ->middleware('permission:laboratory-requests.reject-specimen')
            ->name('specimens.reject');
        Route::post('/items/{item}/results', [LaboratoryWorkQueueController::class, 'enterResult'])
            ->middleware('permission:laboratory-results.enter')
            ->name('items.results.store');
        Route::post('/results/{result}/verify', [LaboratoryWorkQueueController::class, 'verify'])
            ->middleware('permission:laboratory-results.verify')
            ->name('results.verify');
        Route::post('/results/{result}/release', [LaboratoryWorkQueueController::class, 'release'])
            ->middleware('permission:laboratory-results.release')
            ->name('results.release');
        Route::post('/results/{result}/amend', [LaboratoryWorkQueueController::class, 'amend'])
            ->middleware('permission:laboratory-results.amend')
            ->name('results.amend');
        Route::post('/results/{result}/attachments', [LaboratoryWorkQueueController::class, 'uploadAttachment'])
            ->middleware('permission:laboratory-attachments.upload')
            ->name('results.attachments.store');
        Route::get('/results/{result}/attachments/{attachment}/download', [LaboratoryWorkQueueController::class, 'downloadAttachment'])
            ->middleware('permission:laboratory-attachments.download')
            ->name('results.attachments.download');
    });

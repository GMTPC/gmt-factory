<?php

use App\Http\Middleware\RequireAuthentication;
use Illuminate\Support\Facades\Route;
use Modules\Finishing\App\Http\Controllers\FinishingApiController;

Route::middleware(['web', RequireAuthentication::class])->prefix('api/finishing')->name('api.finishing.')->group(function () {
    Route::get('/jobs', [FinishingApiController::class, 'jobs'])->name('jobs.index');
    Route::post('/jobs', [FinishingApiController::class, 'createJob'])->name('jobs.store');
    Route::patch('/jobs/{job}/material', [FinishingApiController::class, 'assignJobMaterial'])->name('jobs.material');
    Route::post('/jobs/{job}/wip-stock', [FinishingApiController::class, 'receiveJobWip'])->name('jobs.wip-stock');
    Route::get('/jobs/{job}/scan-preview', [FinishingApiController::class, 'previewJobScan'])->name('jobs.scan-preview');
    Route::post('/jobs/{job}/scan', [FinishingApiController::class, 'scanJobWip'])->name('jobs.scan');
    Route::patch('/jobs/{job}/complete', [FinishingApiController::class, 'completeJob'])->name('jobs.complete');

    Route::get('/stations', [FinishingApiController::class, 'stations'])->name('stations.index');
    Route::get('/stations/{station}', [FinishingApiController::class, 'station'])->name('stations.show');
    Route::patch('/stations/{station}/machine', [FinishingApiController::class, 'toggleMachine'])->name('stations.machine');

    Route::get('/materials', [FinishingApiController::class, 'materials'])->name('materials.index');
    Route::post('/materials', [FinishingApiController::class, 'createMaterial'])->name('materials.store');
    Route::get('/materials/{material}', [FinishingApiController::class, 'material'])->name('materials.show');
    Route::patch('/materials/{material}/status', [FinishingApiController::class, 'updateMaterialStatus'])->name('materials.status');

    Route::get('/production-logs', [FinishingApiController::class, 'logs'])->name('production-logs.index');
    Route::post('/production-logs', [FinishingApiController::class, 'storeLog'])->name('production-logs.store');
    Route::get('/production-logs/{productionLog}', [FinishingApiController::class, 'showLog'])->name('production-logs.show');
    Route::patch('/production-logs/{productionLog}', [FinishingApiController::class, 'updateLog'])->name('production-logs.update');
    Route::patch('/production-logs/{productionLog}/confirm', [FinishingApiController::class, 'confirmLog'])->name('production-logs.confirm');
    Route::patch('/production-logs/{productionLog}/cancel', [FinishingApiController::class, 'cancelLog'])->name('production-logs.cancel');
    Route::put('/production-logs/{productionLog}/defects', [FinishingApiController::class, 'saveDefectEntries'])->name('production-logs.defects');

    Route::post('/defect-types', [FinishingApiController::class, 'createDefectType'])->name('defect-types.store');
    Route::get('/dashboard/stations', [FinishingApiController::class, 'dashboard'])->name('dashboard.stations');
    Route::get('/reports/daily', [FinishingApiController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/reports/defects', [FinishingApiController::class, 'defectReport'])->name('reports.defects');
    Route::get('/reports/export', [FinishingApiController::class, 'exportReport'])->name('reports.export');
});

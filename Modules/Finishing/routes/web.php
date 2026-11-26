<?php

use App\Http\Middleware\RequireAuthentication;
use Illuminate\Support\Facades\Route;
use Modules\Finishing\App\Http\Controllers\FinishingPageController;

Route::middleware(['web', RequireAuthentication::class])->prefix('finishing')->name('finishing.')->group(function () {
    Route::get('/', [FinishingPageController::class, 'selectStation'])->name('stations.index');
    Route::get('/dashboard', [FinishingPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [FinishingPageController::class, 'reports'])->name('reports');
    Route::get('/daily', [FinishingPageController::class, 'dailySummary'])->name('daily');
    Route::get('/daily/export', [FinishingPageController::class, 'exportDaily'])->name('daily.export');
    Route::get('/jobs/{job}/export', [FinishingPageController::class, 'exportJob'])->whereNumber('job')->name('jobs.export');
    Route::get('/station/{station}/record', [FinishingPageController::class, 'stationJobs'])->where('station', 'ST[1-6]')->name('station.record');
    Route::get('/station/{station}/jobs', [FinishingPageController::class, 'stationJobs'])->where('station', 'ST[1-6]')->name('station.jobs');
    Route::get('/station/{station}/jobs/{job}', [FinishingPageController::class, 'recordJob'])->where('station', 'ST[1-6]')->name('station.jobs.show');
    Route::get('/station/{station}/jobs/{job}/summary', [FinishingPageController::class, 'jobSummaryPage'])->where('station', 'ST[1-6]')->name('station.jobs.summary');
    Route::get('/production-logs/{productionLog}/tag', [FinishingPageController::class, 'printProductionTag'])->whereNumber('productionLog')->name('production.tag');
    Route::get('/station/{station}/history', [FinishingPageController::class, 'history'])->where('station', 'ST[1-6]')->name('station.history');
});

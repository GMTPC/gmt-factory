<?php

use App\Http\Controllers\CutBoard\BxPrintTagController;
use App\Http\Controllers\CutBoard\CutBoardController;
use App\Http\Controllers\CutBoard\CutJobHeaderController;
use App\Http\Controllers\CutBoard\CutJobLineController;
use App\Http\Controllers\CutBoard\CutJobWmsExportController;
use App\Http\Controllers\CutBoard\ForkliftReceiveController;
use App\Http\Controllers\CutBoard\GradeCController;
use App\Http\Controllers\CutBoard\QcLineController;
use App\Http\Controllers\CutBoard\ScanController;
use App\Http\Controllers\EndLineQcController;
use App\Http\Middleware\RequireAuthentication;
use Illuminate\Support\Facades\Route;

Route::middleware(RequireAuthentication::class)->group(function () {

    // End-line QC tag flow (ported from the legacy qc-end-line module).
    Route::get('/end-line-qc/{line}', [EndLineQcController::class, 'index'])->name('end-line-qc.index');
    Route::get('/end-line-qc/{line}/create', [EndLineQcController::class, 'create'])->name('end-line-qc.create');
    Route::post('/end-line-qc/{line}', [EndLineQcController::class, 'store'])->name('end-line-qc.store');
    Route::get('/end-line-qc/{line}/edit/{id}', [EndLineQcController::class, 'edit'])->whereNumber('id')->name('end-line-qc.edit');
    Route::put('/end-line-qc/{line}/update/{id}', [EndLineQcController::class, 'updateHeader'])->whereNumber('id')->name('end-line-qc.update');
    Route::get('/end-line-qc/{line}/{id}/qc', [EndLineQcController::class, 'show'])->whereNumber('id')->name('end-line-qc.qc');
    Route::post('/end-line-qc/{line}/{id}/measure', [EndLineQcController::class, 'measure'])->whereNumber('id')->name('end-line-qc.measure');
    Route::post('/end-line-qc/{line}/{id}/select-spec', [EndLineQcController::class, 'selectSpec'])->whereNumber('id')->name('end-line-qc.select-spec');
    Route::post('/end-line-qc/{line}/{id}/finish', [EndLineQcController::class, 'finish'])->whereNumber('id')->name('end-line-qc.finish');
    Route::post('/end-line-qc/{line}/{id}/status', [EndLineQcController::class, 'updateStatus'])->whereNumber('id')->name('end-line-qc.status');
    Route::delete('/end-line-qc/{line}/{id}', [EndLineQcController::class, 'destroy'])->whereNumber('id')->name('end-line-qc.destroy');
    Route::get('/end-line-qc/{line}/{id}/export', [EndLineQcController::class, 'export'])->whereNumber('id')->name('end-line-qc.export');
    Route::get('/end-line-qc/{line}/{id}/wip-report', [EndLineQcController::class, 'wipReport'])->whereNumber('id')->name('end-line-qc.wip-report');
    Route::get('/end-line-qc/{line}/{id}/fg-report', [EndLineQcController::class, 'fgReport'])->whereNumber('id')->name('end-line-qc.fg-report');
    Route::get('/end-line-qc/wip-export/{id}', [EndLineQcController::class, 'wipExport'])->whereNumber('id')->name('end-line-qc.wip-export');
    Route::get('/end-line-qc/spec/{id}', [EndLineQcController::class, 'specDetails'])->whereNumber('id')->name('end-line-qc.spec-details');
    Route::post('/end-line-qc/measure/{id}', [EndLineQcController::class, 'updateMeasure'])->whereNumber('id')->name('end-line-qc.measure.update');
    Route::delete('/end-line-qc/measure/{id}', [EndLineQcController::class, 'destroyMeasure'])->whereNumber('id')->name('end-line-qc.measure.destroy');
    Route::get('/end-line-qc/tag/{state}/{id}', [EndLineQcController::class, 'printTag'])->whereIn('state', ['wip', 'fg'])->whereNumber('id')->name('end-line-qc.tag');

    Route::get('/cut-boards/lines/{line}/grade-c', [GradeCController::class, 'index'])->name('cut-board.lines.grade-c');
    Route::get('/cut-boards/lines/{line}/grade-c/create', [GradeCController::class, 'create'])->name('cut-board.lines.grade-c.create');
    Route::post('/cut-boards/lines/{line}/grade-c', [GradeCController::class, 'store'])->name('cut-board.lines.grade-c.store');
    Route::get('/cut-boards/lines/{line}/grade-c/print/{processId}/{tranId}', [GradeCController::class, 'printOne'])->name('cut-board.lines.grade-c.print');
    Route::get('/cut-boards/lines/{line}/grade-c/{processId}/print-all', [GradeCController::class, 'printAll'])->name('cut-board.lines.grade-c.print-all');
    Route::get('/cut-boards/lines/{line}/grade-c/{processId}/export-csv', [GradeCController::class, 'exportCsv'])->name('cut-board.lines.grade-c.export');
    Route::get('/cut-boards/lines/{line}/grade-c/{processId}', [GradeCController::class, 'show'])->name('cut-board.lines.grade-c.show');
    Route::put('/cut-boards/lines/{line}/grade-c/{processId}', [GradeCController::class, 'update'])->name('cut-board.lines.grade-c.update');
    Route::post('/cut-boards/lines/{line}/grade-c/{processId}/finish', [GradeCController::class, 'finish'])->name('cut-board.lines.grade-c.finish');
    Route::delete('/cut-boards/lines/{line}/grade-c/{processId}', [GradeCController::class, 'destroy'])->name('cut-board.lines.grade-c.destroy');

    Route::get('/cut-boards/lines/{line}', [QcLineController::class, 'index'])->name('cut-board.lines.show');
    Route::get('/cut-boards/lines/{line}/{section}', [QcLineController::class, 'master'])
        ->where('section', 'history|employees|groups|daily-summary')
        ->name('cut-board.lines.master');
    Route::post('/cut-boards/lines/{line}/jobs', [QcLineController::class, 'storeJob'])->name('cut-board.lines.jobs.store');
    Route::delete('/cut-boards/lines/{line}/jobs/{jobId}', [QcLineController::class, 'deleteJob'])->name('cut-board.lines.jobs.delete');
    Route::post('/cut-boards/lines/{line}/jobs/{jobId}/finish-quality', [QcLineController::class, 'finishQuality'])->name('cut-board.lines.jobs.finish-quality');
    Route::post('/cut-boards/lines/{line}/employees', [QcLineController::class, 'storeEmployees'])->name('cut-board.lines.employees.store');
    Route::delete('/cut-boards/lines/{line}/employees/{employeeId}', [QcLineController::class, 'deleteEmployee'])->name('cut-board.lines.employees.delete');
    Route::post('/cut-boards/lines/{line}/groups', [QcLineController::class, 'storeGroups'])->name('cut-board.lines.groups.store');
    Route::patch('/cut-boards/lines/{line}/groups/{groupId}', [QcLineController::class, 'toggleGroup'])->name('cut-board.lines.groups.toggle');
    Route::post('/cut-boards/lines/{line}/end-shift', [QcLineController::class, 'endShift'])->name('cut-board.lines.end-shift');
    Route::get('/cut-boards/lines/{line}/wip/{jobId}', [QcLineController::class, 'showWork'])->name('cut-board.lines.wip.show');

    Route::get('/cut-boards', [CutBoardController::class, 'index'])->name('wms.cut-boards');
    Route::get('/cut-boards/create', [CutBoardController::class, 'create'])->name('cut-board.jobs.create');
    Route::post('/cut-boards', [CutBoardController::class, 'store'])->name('cut-board.jobs.store');
    Route::get('/cut-boards/{line}/{cutJob}', [CutBoardController::class, 'showForLine'])
        ->where('line', 'L[1-4]')->name('cut-board.lines.jobs.show');
    Route::get('/cut-boards/{cutJob}', [CutBoardController::class, 'show'])->name('cut-board.jobs.show');
    Route::post('/cut-boards/{cutJob}/complete', [CutBoardController::class, 'complete'])->name('cut-board.jobs.complete');
    Route::patch('/cut-boards/{cutJob}/scrap', [CutBoardController::class, 'updateScrap'])->name('cut-board.jobs.scrap');
    Route::post('/cut-boards/{cutJob}/headers', [CutJobHeaderController::class, 'store'])->name('cut-board.headers.store');

    Route::get('/checker-qc-scan', [ScanController::class, 'show'])->name('checker-qc-scan');
    Route::post('/insert-check-csv-qr-code', [ScanController::class, 'store'])->name('insert-check-csv-qr-code');
    Route::get('/forklift-receive', [ForkliftReceiveController::class, 'show'])->name('forklift-receive');
    Route::post('/forklift-receive', [ForkliftReceiveController::class, 'store'])->name('forklift-receive.store');

    Route::get('/bx-print', [BxPrintTagController::class, 'index'])->name('wms.bx-print');
    Route::get('/bx-print-working/{brd_working_id}/{sku_code}', [BxPrintTagController::class, 'show'])->name('wms.bx-print-working');
    Route::get('/bx-print-working-export/{brd_working_id}/{sku_code}', [BxPrintTagController::class, 'export'])->name('wms.bx-print-working-export');
    Route::post('/bx-print-lines/{cutJobLine}/printed', [BxPrintTagController::class, 'markPrinted'])->name('wms.bx-print-lines.printed');
    Route::post('/cut-board-lines/{cutJobLine}/reject', [CutJobLineController::class, 'reject'])->name('cut-board.lines.reject');
    Route::post('/cut-board-lines/{cutJobLine}/wms-export/retry', [CutJobWmsExportController::class, 'retry'])->name('cut-board.lines.wms-export.retry');

});

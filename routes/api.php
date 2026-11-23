<?php

use App\Http\Controllers\Api\CutBoardApiController;
use App\Http\Middleware\RequireAuthentication;
use Illuminate\Support\Facades\Route;

// These API endpoints are called by the authenticated Vue page with same-origin fetch.
Route::middleware(['web', RequireAuthentication::class])->group(function (): void {
    Route::post('/cut-boards/{line}/{cutJob}/wip', [CutBoardApiController::class, 'scanWip'])
        ->where('line', 'L[1-4]')->name('api.cut-board.wip.store');
    Route::post('/cut-boards/{line}/{cutJob}/fg', [CutBoardApiController::class, 'issueFg'])
        ->where('line', 'L[1-4]')->name('api.cut-board.fg.store');
    Route::patch('/cut-board-lines/{cutJobLine}', [CutBoardApiController::class, 'updateLine'])
        ->name('api.cut-board.lines.update');
});

<?php

use App\Http\Controllers\Master\MasterProductGroupController;
use App\Http\Controllers\Master\MasterProductSpecController;
use App\Http\Controllers\Master\MasterLookupController;
use App\Http\Middleware\RequireAuthentication;
use Illuminate\Support\Facades\Route;

Route::middleware(RequireAuthentication::class)->group(function (): void {
    Route::resource('master-product-groups', MasterProductGroupController::class)
        ->parameters(['master-product-groups' => 'masterProductGroup'])
        ->except(['show']);

    Route::resource('master-product-specs', MasterProductSpecController::class)
        ->parameters(['master-product-specs' => 'masterProductSpec'])
        ->except(['show']);

    foreach ([
        'brands' => 'brands',
        'edges' => 'edges',
        'faces' => 'faces',
        'sheet-types' => 'sheet-types',
        'sizes' => 'sizes',
        'thicknesses' => 'thicknesses',
    ] as $slug => $catalog) {
        Route::controller(MasterLookupController::class)
            ->prefix('master-'.$slug)
            ->name('master-'.$slug.'.')
            ->group(function () use ($catalog): void {
                Route::get('/', 'index')->defaults('catalog', $catalog)->name('index');
                Route::get('/create', 'create')->defaults('catalog', $catalog)->name('create');
                Route::post('/', 'store')->defaults('catalog', $catalog)->name('store');
                Route::get('/{item}/edit', 'edit')->defaults('catalog', $catalog)->name('edit');
                Route::put('/{item}', 'update')->defaults('catalog', $catalog)->name('update');
                Route::delete('/{item}', 'destroy')->defaults('catalog', $catalog)->name('destroy');
            });
    }
});

<?php

use App\Http\Controllers\Api\Table\RestaurantTableController;
use Illuminate\Support\Facades\Route;

Route::prefix('table')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::prefix('tables')
            ->controller(RestaurantTableController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{slug}', 'show');
                Route::post('/', 'store');
                Route::patch('/{slug}', 'update');
                Route::delete('/{slug}', 'destroy');
            });
    });

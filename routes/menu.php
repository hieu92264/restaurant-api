<?php

use App\Http\Controllers\Api\Menu\CategoryController;
use App\Http\Controllers\Api\Menu\DishController;
use Illuminate\Support\Facades\Route;

Route::prefix('menu')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::prefix('categories')
            ->controller(CategoryController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{category}', 'show');
                Route::post('/', 'store');
                Route::patch('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });

        Route::prefix('dishes')
            ->controller(DishController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{dish}', 'show');
                Route::post('/', 'store');
                Route::patch('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
    });

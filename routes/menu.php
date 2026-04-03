<?php

use App\Http\Controllers\Api\Menu\CategoryController;
use App\Http\Controllers\Api\Menu\ComboController;
use App\Http\Controllers\Api\Menu\DiscountController;
use App\Http\Controllers\Api\Menu\DishController;
use Illuminate\Support\Facades\Route;

Route::prefix('menu')
    ->middleware(['auth:api'])
    ->group(function () {
        Route::prefix('categories')
            ->controller(CategoryController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{slug}', 'show');
                Route::post('/', 'store');
                Route::patch('/{slug}', 'update');
                Route::delete('/{slug}', 'destroy');
            });

        Route::prefix('dishes')
            ->controller(DishController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{slug}', 'show');
                Route::post('/', 'store');
                Route::post('/{slug}', 'update');
                Route::patch('/{slug}', 'update');
                Route::delete('/{slug}', 'destroy');
            });

        Route::prefix('discounts')
            ->controller(DiscountController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{slug}', 'show');
                Route::post('/', 'store');
                Route::patch('/{slug}', 'update');
                Route::delete('/{slug}', 'destroy');
            });

        Route::prefix('combos')
            ->controller(ComboController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{slug}', 'show');
                Route::post('/', 'store');
                Route::patch('/{slug}', 'update');
                Route::delete('/{slug}', 'destroy');
            });
    });

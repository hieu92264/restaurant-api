<?php

use App\Http\Controllers\Api\Menu\CategoryController;
use App\Http\Controllers\Api\Menu\ComboController;
use App\Http\Controllers\Api\Menu\DiscountController;
use App\Http\Controllers\Api\Menu\DishController;
use App\Http\Controllers\Api\Table\ReservationController;
use Illuminate\Support\Facades\Route;

Route::prefix('menu')
    ->group(function () {
        Route::prefix('dishes')
            ->controller(DishController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{slug}', 'show');
            });

        Route::prefix('combos')
            ->controller(ComboController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{slug}', 'show');
            });

        Route::prefix('reservations')
            ->controller(ReservationController::class)
            ->group(function () {
                Route::post('/store-by-customer', 'storeByCustomer');
            });

        Route::middleware(['auth:api'])->group(function () {
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
                    Route::post('/', 'store');
                    Route::put('/{slug}', 'update');
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
                    Route::post('/', 'store');
                    Route::patch('/{slug}', 'update');
                    Route::delete('/{slug}', 'destroy');
                });

            Route::prefix('reservations')
                ->controller(ReservationController::class)
                ->group(function () {
                    Route::get('/', 'index');
                    Route::get('/{slug}', 'show');
                    Route::post('/', 'store');
                    Route::patch('/{slug}', 'update');
                    Route::delete('/{slug}', 'destroy');
                });
        });
    });

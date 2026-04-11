<?php

use App\Http\Controllers\Api\Table\CartOrderController;
use App\Http\Controllers\Api\Table\InvoiceController;
use App\Http\Controllers\Api\Table\ReservationController;
use App\Http\Controllers\Api\Table\RestaurantTableController;
use App\Http\Controllers\Api\Table\TableSessionController;
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

        Route::prefix('sessions')
            ->controller(TableSessionController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{tableSessionId}', 'show');
                Route::post('/', 'store');
                Route::patch('/{tableSessionId}', 'update');
            });

        Route::prefix('cart-orders')
            ->controller(CartOrderController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/current-by-table/{tableId}', 'showCurrentByTable');
                Route::get('/{cartOrderId}', 'show');
                Route::post('/', 'store');
                Route::patch('/{cartOrderId}', 'update');
                Route::delete('/{cartOrderId}', 'destroy');
            });

        Route::prefix('invoices')
            ->controller(InvoiceController::class)
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{invoiceId}', 'show');
                Route::post('/', 'store');
                Route::patch('/{invoiceId}', 'update');
                Route::delete('/{invoiceId}', 'destroy');
            });
    });

Route::prefix('reservations')
    ->controller(ReservationController::class)
    ->group(function () {
        Route::post('/store-by-customer', 'storeByCustomer');
        Route::get('/{slug}', 'show');

        Route::middleware(['auth:api'])->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::patch('/{slug}', 'update');
            Route::delete('/{slug}', 'destroy');
        });
    });

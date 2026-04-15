<?php

use App\Http\Controllers\Api\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('statistics')
    ->middleware(['auth:api'])
    ->controller(StatisticsController::class)
    ->group(function () {
        Route::get('/monthly-revenue', 'monthlyRevenue');
        Route::get('/revenue', 'revenue');
        Route::get('/revenue-chart', 'revenueChart');
        Route::get('/average-service-time', 'averageServiceTime');
        Route::get('/top-dishes', 'topDishesReport');
        Route::get('/top-combos', 'topCombosReport');
        Route::get('/summary', 'summary');
    });

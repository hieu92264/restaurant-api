<?php

use App\Http\Controllers\Api\Menu\CookingMethodController;
use App\Http\Controllers\Api\Menu\ItemTypeController;
use App\Http\Controllers\Api\Menu\MenuCategoryController;
use App\Http\Controllers\Api\Menu\OptionGroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('menu')->group(function () {
    Route::prefix('item-types')->controller(ItemTypeController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/store', 'store');
            Route::patch('/update/{id}', 'update');
            Route::delete('/delete/{id}', 'destroy');
        });

    Route::prefix('cooking-methods')->controller(CookingMethodController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/store', 'store');
            Route::patch('/update/{id}', 'update');
            Route::delete('/delete/{id}', 'destroy');
        });

    Route::prefix('menu-categories')->controller(MenuCategoryController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/store', 'store');
            Route::patch('/update/{id}', 'update');
            Route::delete('/delete/{id}', 'destroy');
        });

    Route::prefix('option-groups')->controller(OptionGroupController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/store', 'store');
            Route::patch('/update/{id}', 'update');
            Route::delete('/delete/{id}', 'destroy');
        });
});

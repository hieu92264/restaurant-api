<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->middleware(['auth:api'])
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('role:MANAGER');
        Route::post('/', 'store')->middleware('role:');
        Route::patch('/{user}', 'update')->middleware('role:MANAGER');
        Route::delete('/{user}', 'destroy')->middleware('role:');
    });

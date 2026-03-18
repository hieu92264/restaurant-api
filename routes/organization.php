<?php

use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->middleware(['auth:api'])
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('role:MANAGER');
        Route::get('/{user}', 'show')->middleware('role:MANAGER');
        Route::post('/', 'store')->middleware('role:');
        Route::patch('/{user}', 'update')->middleware('role:MANAGER');
        Route::delete('/{user}', 'destroy')->middleware('role:');
    });

Route::prefix('roles')
    ->middleware(['auth:api'])
    ->controller(RoleController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('role:MANAGER');
        Route::get('/{role}', 'show')->middleware('role:MANAGER');
        Route::post('/', 'store')->middleware('role:');
        Route::patch('/{role}', 'update')->middleware('role:');
        Route::delete('/{role}', 'destroy')->middleware('role:');
    });

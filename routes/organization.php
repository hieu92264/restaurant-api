<?php

use App\Http\Controllers\Api\Auth\RoleController;
use App\Http\Controllers\Api\Auth\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->middleware(['auth:api'])
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('role:MANAGER');
        Route::get('/{user}', 'show')->middleware('role:MANAGER');
        Route::post('/', 'store')->middleware('role:');
        Route::patch('/{id}', 'update')->middleware('role:MANAGER');
        Route::delete('/{id}', 'destroy')->middleware('role:');
    });

Route::prefix('roles')
    ->middleware(['auth:api'])
    ->controller(RoleController::class)
    ->group(function () {
        Route::get('/', 'index')->middleware('role:MANAGER');
        Route::get('/{role}', 'show')->middleware('role:MANAGER');
        Route::post('/', 'store')->middleware('role:');
        Route::patch('/{id}', 'update')->middleware('role:');
        Route::delete('/{id}', 'destroy')->middleware('role:');
    });

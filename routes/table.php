<?php

use Illuminate\Support\Facades\Route;

Route::prefix('table')
    ->middleware(['auth:api'])
    ->group(function () {});

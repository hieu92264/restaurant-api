<?php

namespace App\Http\Controllers;

use App\Common\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use ApiResponse;

    protected function getUserName(): string
    {
        return Auth::user()->user_name;
    }
}

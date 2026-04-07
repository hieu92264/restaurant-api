<?php

namespace App\Http\Controllers\Api\Table;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;

class TableSessionController extends Controller
{
    public function index(?string $tableSlug): JsonResponse
    {
        $table = $tableSlug ?? RestaurantTable::with(['cartOrders'])->where('slug', $tableSlug)->firstOrFail();

    }
}

<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\RestaurantTableStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends BaseModel
{
    protected $fillable = [
        'area_id',
        'code',
        'name',
        'capacity',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'area_id' => 'integer',
            'capacity' => 'integer',
            'status' => 'string',
            'is_active' => 'string',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(TableArea::class, 'area_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class, 'table_id');
    }

    public function cartOrders(): HasMany
    {
        return $this->hasMany(CartOrder::class, 'table_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'table_id');
    }
}

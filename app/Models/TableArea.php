<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableArea extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'string',
        ];
    }

    public function restaurantTables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class, 'area_id');
    }
}

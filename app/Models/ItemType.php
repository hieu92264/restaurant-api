<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemType extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'string',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(MenuCategory::class, 'item_type_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'item_type_id');
    }
}

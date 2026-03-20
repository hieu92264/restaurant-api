<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CookingMethod extends BaseModel
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

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'cooking_method_id');
    }
}

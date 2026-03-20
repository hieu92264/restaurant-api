<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends BaseModel
{
    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'name',
        'remark',
        'base_price',
        'is_active',
        'is_customize_allowed',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'string',
            'is_customize_allowed' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function groups(): HasMany
    {
        return $this->hasMany(ComboGroup::class, 'combo_id');
    }

    public function cartOrderItems(): HasMany
    {
        return $this->hasMany(CartOrderItem::class, 'combo_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'combo_id');
    }
}

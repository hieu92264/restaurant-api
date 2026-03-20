<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\MenuItemStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItemVariant extends BaseModel
{
    const UPDATED_AT = null;

    protected $fillable = [
        'menu_item_id',
        'sku_code',
        'name',
        'size_code',
        'price',
        'cost_price',
        'is_default',
        'status',
        'compare_at_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'menu_item_id' => 'integer',
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_default' => 'boolean',
            'status' => 'string',
            'compare_at_price' => 'decimal:2',
            'created_at' => 'datetime',
            'is_active' => 'string',
        ];
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            OptionGroup::class,
            'variant_option_groups',
            'variant_id',
            'option_group_id'
        )->withPivot(['display_order', 'is_active']);
    }

    public function comboGroupItems(): HasMany
    {
        return $this->hasMany(ComboGroupItem::class, 'variant_id');
    }

    public function cartOrderItems(): HasMany
    {
        return $this->hasMany(CartOrderItem::class, 'variant_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'variant_id');
    }
}

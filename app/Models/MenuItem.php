<?php

namespace App\Models;

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\MenuItemStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends BaseModel
{
    protected $fillable = [
        'item_type_id',
        'category_id',
        'cooking_method_id',
        'code',
        'name',
        'base_unit',
        'remark',
        'image_url',
        'kitchen_print_name',
        'is_featured',
        'is_new_item',
        'sort_order',
        'available_from',
        'available_to',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'item_type_id' => 'integer',
            'category_id' => 'integer',
            'cooking_method_id' => 'integer',
            'is_featured' => 'boolean',
            'is_new_item' => 'boolean',
            'sort_order' => 'integer',
            'available_from' => 'string',
            'available_to' => 'string',
            'status' => 'string',
            'is_active' => 'string',
        ];
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function cookingMethod(): BelongsTo
    {
        return $this->belongsTo(CookingMethod::class, 'cooking_method_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MenuItemVariant::class, 'menu_item_id');
    }

    public function comboGroupItems(): HasMany
    {
        return $this->hasMany(ComboGroupItem::class, 'menu_item_id');
    }

    public function cartOrderItems(): HasMany
    {
        return $this->hasMany(CartOrderItem::class, 'menu_item_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'menu_item_id');
    }
}

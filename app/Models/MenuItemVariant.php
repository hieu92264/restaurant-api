<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\MenuItemStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $menu_item_id
 * @property string $sku_code
 * @property string $name
 * @property string|null $size_code
 * @property numeric $price
 * @property numeric|null $cost_price
 * @property bool $is_default
 * @property string $status
 * @property numeric|null $compare_at_price
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItem> $cartOrderItems
 * @property-read int|null $cart_order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComboGroupItem> $comboGroupItems
 * @property-read int|null $combo_group_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $invoiceItems
 * @property-read int|null $invoice_items_count
 * @property-read \App\Models\MenuItem $menuItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OptionGroup> $optionGroups
 * @property-read int|null $option_groups_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereCompareAtPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereCostPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereSizeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereSkuCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItemVariant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MenuItemVariant extends BaseModel
{
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
            'updated_at' => 'datetime',
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

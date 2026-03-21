<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\MenuItemStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $item_type_id
 * @property int $category_id
 * @property int|null $cooking_method_id
 * @property string $code
 * @property string $name
 * @property string|null $base_unit
 * @property string|null $remark
 * @property string|null $image_url
 * @property string|null $kitchen_print_name
 * @property bool $is_featured
 * @property bool $is_new_item
 * @property int $sort_order
 * @property string|null $available_from
 * @property string|null $available_to
 * @property string $status
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItem> $cartOrderItems
 * @property-read int|null $cart_order_items_count
 * @property-read \App\Models\MenuCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComboGroupItem> $comboGroupItems
 * @property-read int|null $combo_group_items_count
 * @property-read \App\Models\CookingMethod|null $cookingMethod
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $invoiceItems
 * @property-read int|null $invoice_items_count
 * @property-read \App\Models\ItemType $itemType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuItemVariant> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereAvailableFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereAvailableTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereBaseUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCookingMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereIsNewItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereItemTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereKitchenPrintName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

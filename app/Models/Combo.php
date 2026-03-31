<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $remark
 * @property numeric $base_price
 * @property bool $is_active
 * @property bool $is_customize_allowed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItem> $cartOrderItems
 * @property-read int|null $cart_order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComboGroup> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $invoiceItems
 * @property-read int|null $invoice_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereIsCustomizeAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Combo extends BaseModel
{
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
            'is_active' => 'boolean',
            'is_customize_allowed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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

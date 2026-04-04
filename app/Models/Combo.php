<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $remark
 * @property array<string, mixed>|null $combo_image
 * @property int $combo_price
 * @property bool $is_active
 * @property int $max_use_times
 * @property string|null $tag
 * @property array<int, string>|null $days_in_week
 * @property string|null $start_time
 * @property string|null $end_time
 * @property \Illuminate\Support\Carbon|null $start_at
 * @property \Illuminate\Support\Carbon|null $end_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrderItem> $cartOrderItems
 * @property-read int|null $cart_order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ComboDish> $comboDishes
 * @property-read int|null $combo_dishes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dish> $dishes
 * @property-read int|null $dishes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $invoiceItems
 * @property-read int|null $invoice_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereComboPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Combo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Combo extends BaseModel
{
    protected $fillable = [
        'is_active',
        'slug',
        'name',
        'remark',
        'combo_image',
        'combo_price',
        'max_use_times',
        'tag',
        'days_in_week',
        'start_time',
        'end_time',
        'start_at',
        'end_at',
    ];

    protected $appends = [
        'selling_price',
    ];

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'combo_dishes', 'combo_id', 'dish_id')
            ->withPivot(['quantity', 'sort_order', 'is_active'])
            ->withTimestamps();
    }

    public function cartOrderItems(): HasMany
    {
        return $this->hasMany(CartOrderItem::class, 'combo_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'combo_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function sellingPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) round($this->comboDishes()
                ->where('combo_dishes.is_active', true)
                ->join('dishes', 'combo_dishes.dish_id', '=', 'dishes.id')
                ->sum(DB::raw('dishes.price * combo_dishes.quantity')))
        );
    }

    protected function comboImage(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_string($value) ? json_decode($value, true) : $value,
            set: fn($value) => is_array($value)
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $value
        );
    }

    public function comboDishes(): HasMany
    {
        return $this->hasMany(ComboDish::class, 'combo_id');
    }

    protected function casts(): array
    {
        return [
            'combo_price' => 'integer',
            'seling_price' => 'integer',
            'is_active' => 'boolean',
            'max_use_times' => 'integer',
            'tag' => 'string',
            'days_in_week' => 'array',
            'start_time' => 'string',
            'end_time' => 'string',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

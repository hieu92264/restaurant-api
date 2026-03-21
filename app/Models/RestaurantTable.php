<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\RestaurantTableStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $area_id
 * @property string $code
 * @property string $name
 * @property int $capacity
 * @property string $status
 * @property string $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TableArea|null $area
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrder> $cartOrders
 * @property-read int|null $cart_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TableSession> $sessions
 * @property-read int|null $sessions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RestaurantTable extends BaseModel
{
    protected $fillable = [
        'area_id',
        'code',
        'name',
        'capacity',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'area_id' => 'integer',
            'capacity' => 'integer',
            'status' => 'string',
            'is_active' => 'string',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(TableArea::class, 'area_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class, 'table_id');
    }

    public function cartOrders(): HasMany
    {
        return $this->hasMany(CartOrder::class, 'table_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'table_id');
    }
}

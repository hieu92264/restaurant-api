<?php

namespace App\Models;

use App\Common\Constants\ReservationStatus;
use App\Common\Constants\RestaurantTableStatus;
use App\Common\Constants\TableSessionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property int $capacity
 * @property int $sort_order
 * @property string $status
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartOrder> $cartOrders
 * @property-read int|null $cart_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\Reservation|null $holdingReservation
 * @property-read \App\Models\Reservation|null $reservation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TableSession> $sessions
 * @property-read int|null $sessions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RestaurantTable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RestaurantTable extends BaseModel
{
    protected $appends = [
        'status',
    ];

    protected $hidden = [
        'has_live_session',
        'has_holding_reservation',
        'holdingReservation',
    ];

    protected $fillable = [
        'slug',
        'name',
        'capacity',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeWithComputedStatus(Builder $query): Builder
    {
        $now = now();

        return $query->withExists([
            'sessions as has_live_session' => fn (Builder $builder) => $builder
                ->where('is_active', true)
                ->whereIn('status', [
                    TableSessionStatus::OPEN,
                    TableSessionStatus::PAYMENT_PENDING,
                ]),
            'reservations as has_holding_reservation' => fn (Builder $builder) => $builder
                ->where('is_active', true)
                ->whereIn('status', [
                    ReservationStatus::PENDING,
                    ReservationStatus::CONFIRMED,
                ])
                ->whereNotNull('hold_start_time')
                ->whereNotNull('hold_end_time')
                ->where('hold_start_time', '<=', $now)
                ->where('hold_end_time', '>=', $now),
        ]);
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

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_code', 'slug');
    }

    public function holdingReservation(): HasOne
    {
        $now = now();

        return $this->hasOne(Reservation::class, 'table_code', 'slug')
            ->ofMany([
                'hold_start_time' => 'max',
                'id' => 'max',
            ], function (Builder $builder) use ($now) {
                $builder
                    ->where('is_active', true)
                    ->whereIn('status', [
                        ReservationStatus::PENDING,
                        ReservationStatus::CONFIRMED,
                    ])
                    ->whereNotNull('hold_start_time')
                    ->whereNotNull('hold_end_time')
                    ->where('hold_start_time', '<=', $now)
                    ->where('hold_end_time', '>=', $now);
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolveStatus()
        );
    }

    protected function reservation(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolveStatus() === RestaurantTableStatus::RESERVED
                ? $this->resolveHoldingReservation()
                : null
        );
    }

    public function resolveStatus(): string
    {
        if ($this->hasLiveSession()) {
            return RestaurantTableStatus::OCCUPIED;
        }

        if ($this->hasHoldingReservation()) {
            return RestaurantTableStatus::RESERVED;
        }

        return RestaurantTableStatus::AVAILABLE;
    }

    protected function hasLiveSession(): bool
    {
        if (array_key_exists('has_live_session', $this->attributes)) {
            return (bool) $this->attributes['has_live_session'];
        }

        if ($this->relationLoaded('sessions')) {
            return $this->sessions->contains(fn (TableSession $session) => $session->is_active
                && in_array($session->status, [
                    TableSessionStatus::OPEN,
                    TableSessionStatus::PAYMENT_PENDING,
                ], true));
        }

        return $this->sessions()
            ->where('is_active', true)
            ->whereIn('status', [
                TableSessionStatus::OPEN,
                TableSessionStatus::PAYMENT_PENDING,
            ])
            ->exists();
    }

    protected function hasHoldingReservation(): bool
    {
        $now = now();

        if (array_key_exists('has_holding_reservation', $this->attributes)) {
            return (bool) $this->attributes['has_holding_reservation'];
        }

        if ($this->relationLoaded('reservations')) {
            return $this->reservations->contains(fn (Reservation $reservation) => $reservation->is_active
                && in_array($reservation->status, [
                    ReservationStatus::PENDING,
                    ReservationStatus::CONFIRMED,
                ], true)
                && $reservation->hold_start_time !== null
                && $reservation->hold_end_time !== null
                && $reservation->hold_start_time->lessThanOrEqualTo($now)
                && $reservation->hold_end_time->greaterThanOrEqualTo($now));
        }

        return $this->reservations()
            ->where('is_active', true)
            ->whereIn('status', [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
            ])
            ->whereNotNull('hold_start_time')
            ->whereNotNull('hold_end_time')
            ->where('hold_start_time', '<=', $now)
            ->where('hold_end_time', '>=', $now)
            ->exists();
    }

    protected function resolveHoldingReservation(): ?Reservation
    {
        if ($this->relationLoaded('holdingReservation')) {
            return $this->getRelation('holdingReservation');
        }

        return $this->holdingReservation()->first();
    }
}

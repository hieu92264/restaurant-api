<?php

namespace App\Models;

use App\Common\Constants\ActiveStatus;
use App\Common\Constants\TableSessionStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableSession extends BaseModel
{
    protected $fillable = [
        'table_id',
        'opened_by_employee_id',
        'closed_by_employee_id',
        'guest_count',
        'status',
        'opened_at',
        'closed_at',
        'remark',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'table_id' => 'integer',
            'opened_by_employee_id' => 'integer',
            'closed_by_employee_id' => 'integer',
            'guest_count' => 'integer',
            'status' => 'string',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_active' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function openedByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_employee_id');
    }

    public function closedByEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_employee_id');
    }

    public function cartOrders(): HasMany
    {
        return $this->hasMany(CartOrder::class, 'session_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'session_id');
    }
}

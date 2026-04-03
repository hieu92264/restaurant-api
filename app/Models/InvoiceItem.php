<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $combo_id
 * @property string $item_name_snapshot
 * @property string|null $variant_name_snapshot
 * @property numeric $quantity
 * @property numeric $base_unit_price
 * @property numeric $option_total_price
 * @property numeric $unit_final_price
 * @property numeric $line_total
 * @property string|null $item_note
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Combo|null $combo
 * @property-read \App\Models\Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereBaseUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereComboId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereItemNameSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereItemNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereOptionTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUnitFinalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereVariantNameSnapshot($value)
 * @mixin \Eloquent
 */
class InvoiceItem extends BaseModel
{
    protected $fillable = [
        'invoice_id',
        'combo_id',
        'item_name_snapshot',
        'variant_name_snapshot',
        'quantity',
        'base_unit_price',
        'option_total_price',
        'unit_final_price',
        'line_total',
        'item_note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'invoice_id' => 'integer',
            'combo_id' => 'integer',
            'quantity' => 'decimal:2',
            'base_unit_price' => 'integer',
            'option_total_price' => 'integer',
            'unit_final_price' => 'integer',
            'line_total' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }
}

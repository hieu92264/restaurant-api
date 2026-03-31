<?php

namespace Database\Seeders;

use App\Models\Combo;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;

class InvoiceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoice = Invoice::withoutGlobalScopes()->where('no', 'INV-20260320-000001')->first();

        $items = [
            ['combo_code' => null, 'item_name_snapshot' => 'Tra dao cam sa', 'variant_name_snapshot' => 'Size M', 'quantity' => 1, 'base_unit_price' => 45000, 'option_total_price' => 0, 'unit_final_price' => 45000, 'line_total' => 45000, 'item_note' => null],
            ['combo_code' => null, 'item_name_snapshot' => 'Coca cola lon', 'variant_name_snapshot' => null, 'quantity' => 2, 'base_unit_price' => 18000, 'option_total_price' => 0, 'unit_final_price' => 18000, 'line_total' => 36000, 'item_note' => null],
            ['combo_code' => 'CB_TRUA_A', 'item_name_snapshot' => 'Combo trua 2 nguoi', 'variant_name_snapshot' => 'Com rang hai san', 'quantity' => 1, 'base_unit_price' => 16000, 'option_total_price' => 0, 'unit_final_price' => 16000, 'line_total' => 16000, 'item_note' => 'Bo sung trong combo'],
        ];

        foreach ($items as $item) {
            $combo = $item['combo_code']
                ? Combo::withoutGlobalScopes()->where('code', $item['combo_code'])->first()
                : null;

            InvoiceItem::withoutGlobalScopes()->updateOrCreate(
                [
                    'invoice_id' => $invoice?->id,
                    'item_name_snapshot' => $item['item_name_snapshot'],
                    'variant_name_snapshot' => $item['variant_name_snapshot'],
                ],
                [
                    'combo_id' => $combo?->id,
                    'quantity' => $item['quantity'],
                    'base_unit_price' => $item['base_unit_price'],
                    'option_total_price' => $item['option_total_price'],
                    'unit_final_price' => $item['unit_final_price'],
                    'line_total' => $item['line_total'],
                    'item_note' => $item['item_note'],
                    'is_active' => true,
                ]
            );
        }
    }
}

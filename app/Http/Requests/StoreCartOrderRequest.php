<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCartOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'exists:table_sessions,id'],
            'table_id' => ['required', 'integer', 'exists:restaurant_tables,id'],
            'subtotal_amount' => ['required', 'integer', 'min:0'],
            'discount_amount' => ['sometimes', 'integer', 'min:0'],
            'service_charge_amount' => ['sometimes', 'integer', 'min:0'],
            'tax_amount' => ['sometimes', 'integer', 'min:0'],
            'total_amount' => ['required', 'integer', 'min:0'],
            'remark' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.combo_id' => ['nullable', 'integer', 'exists:combos,id'],
            'items.*.item_name_snapshot' => ['required', 'string', 'max:150'],
            'items.*.variant_name_snapshot' => ['nullable', 'string', 'max:150'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.base_unit_price' => ['required', 'integer', 'min:0'],
            'items.*.option_total_price' => ['sometimes', 'integer', 'min:0'],
            'items.*.unit_final_price' => ['required', 'integer', 'min:0'],
            'items.*.line_total' => ['required', 'integer', 'min:0'],
            'items.*.item_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}

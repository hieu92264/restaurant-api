<?php

namespace App\Http\Requests;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\OrderLineStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCartOrderRequest extends FormRequest
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
            'session_id' => ['sometimes', 'required', 'integer', 'exists:table_sessions,id'],
            'table_id' => ['sometimes', 'required', 'integer', 'exists:restaurant_tables,id'],
            'status' => ['sometimes', 'required', 'string', Rule::in(CartOrderStatus::values())],
            'subtotal_amount' => ['sometimes', 'required', 'integer', 'min:0'],
            'discount_amount' => ['sometimes', 'required', 'integer', 'min:0'],
            'service_charge_amount' => ['sometimes', 'required', 'integer', 'min:0'],
            'tax_amount' => ['sometimes', 'required', 'integer', 'min:0'],
            'total_amount' => ['sometimes', 'required', 'integer', 'min:0'],
            'remark' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],

            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.combo_id' => ['nullable', 'integer', 'exists:combos,id'],
            'items.*.item_name_snapshot' => ['required_with:items', 'string', 'max:150'],
            'items.*.variant_name_snapshot' => ['nullable', 'string', 'max:150'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0'],
            'items.*.base_unit_price' => ['required_with:items', 'integer', 'min:0'],
            'items.*.option_total_price' => ['sometimes', 'integer', 'min:0'],
            'items.*.unit_final_price' => ['required_with:items', 'integer', 'min:0'],
            'items.*.line_total' => ['required_with:items', 'integer', 'min:0'],
            'items.*.item_note' => ['nullable', 'string', 'max:255'],
            'items.*.line_status' => ['sometimes', 'required', 'string', Rule::in(OrderLineStatus::values())],
            'items.*.is_active' => ['sometimes', 'boolean'],
        ];
    }
}

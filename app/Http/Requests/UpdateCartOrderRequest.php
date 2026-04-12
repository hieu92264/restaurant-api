<?php

namespace App\Http\Requests;

use App\Common\Constants\CartOrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCartOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! is_array($this->input('items'))) {
            return;
        }

        $items = array_map(function ($item) {
            if (! is_array($item)) {
                return $item;
            }

            if (isset($item['item_type']) && is_string($item['item_type'])) {
                $item['item_type'] = strtoupper($item['item_type']);
            }

            return $item;
        }, $this->input('items'));

        $this->merge([
            'items' => $items,
        ]);
    }

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

            'items' => ['sometimes', 'array'],
            'items.*.item_type' => ['required_with:items', 'string', Rule::in(['COMBO', 'DISH'])],
            'items.*.item_id' => ['required_with:items', 'integer', 'min:1'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0'],
        ];
    }
}

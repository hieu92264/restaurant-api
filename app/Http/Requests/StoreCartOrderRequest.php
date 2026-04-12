<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartOrderRequest extends FormRequest
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
            'session_id' => ['required', 'integer', 'exists:table_sessions,id'],
            'table_id' => ['required', 'integer', 'exists:restaurant_tables,id'],
            'discount_amount' => ['sometimes', 'integer', 'min:0'],
            'service_charge_amount' => ['sometimes', 'integer', 'min:0'],
            'tax_amount' => ['sometimes', 'integer', 'min:0'],
            'remark' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'string', Rule::in(['COMBO', 'DISH'])],
            'items.*.item_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }
}

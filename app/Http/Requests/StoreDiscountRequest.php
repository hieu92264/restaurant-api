<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['nullable', 'boolean'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'scope' => ['nullable', Rule::in(['dish', 'invoice'])],
            'discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'max_use_times' => ['nullable', 'integer', 'min:0'],
            'discount_value' => ['required', 'integer', 'min:0'],
            'min_order_value' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'numeric'],
            'dish_slugs' => ['nullable', 'array', Rule::requiredIf(fn () => $this->input('scope', 'dish') === 'dish')],
            'dish_slugs.*' => ['string', 'distinct', 'exists:dishes,slug'],
        ];
    }
}

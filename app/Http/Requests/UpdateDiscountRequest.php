<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountRequest extends FormRequest
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
            'is_active' => ['sometimes', 'boolean'],
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'scope' => ['sometimes', Rule::in(['dish', 'invoice'])],
            'discount_type' => ['sometimes', Rule::in(['percentage', 'fixed'])],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_use_times' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'discount_value' => ['sometimes', 'integer', 'min:0'],
            'min_order_value' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sort_order' => ['sometimes', 'nullable', 'numeric'],
            'dish_slugs' => ['sometimes', 'nullable', 'array'],
            'dish_slugs.*' => ['string', 'distinct', 'exists:dishes,slug'],
        ];
    }
}

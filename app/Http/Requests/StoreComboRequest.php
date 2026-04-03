<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComboRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'remark' => ['nullable', 'string'],
            'base_price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'max_use_times' => ['nullable', 'integer', 'min:0'],
            'dishes' => ['nullable', 'array'],
            'dishes.*.dish_slug' => ['required_with:dishes', 'string', 'distinct', 'exists:dishes,slug'],
            'dishes.*.quantity' => ['nullable', 'numeric', 'gt:0'],
            'dishes.*.sort_order' => ['nullable', 'integer'],
            'dishes.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Common\Constants\ComboTag;
use App\Common\Constants\DayInWeek;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComboRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:150'],
            'remark' => ['sometimes', 'nullable', 'string'],
            'combo_price' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'tag' => ['sometimes', 'nullable', Rule::in(ComboTag::values())],
            'days_in_week' => ['sometimes', 'nullable', 'array'],
            'days_in_week.*' => ['string', Rule::in(DayInWeek::values())],
            'start_time' => ['sometimes', 'nullable', 'date'],
            'end_time' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_time'],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'end_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_at'],
            'max_use_times' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'dishes' => ['sometimes', 'nullable', 'array'],
            'dishes.*.dish_slug' => ['required_with:dishes', 'string', 'distinct', 'exists:dishes,slug'],
            'dishes.*.quantity' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'dishes.*.sort_order' => ['sometimes', 'nullable', 'integer'],
            'dishes.*.is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}

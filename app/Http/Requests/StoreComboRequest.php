<?php

namespace App\Http\Requests;

use App\Common\Constants\ComboTag;
use App\Common\Constants\DayInWeek;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'combo_price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'tag' => ['nullable', Rule::in(ComboTag::values())],
            'days_in_week' => ['nullable', 'array'],
            'days_in_week.*' => ['string', Rule::in(DayInWeek::values())],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
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

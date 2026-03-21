<?php

namespace App\Http\Requests;

use App\Common\Constants\SelectionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOptionGroupRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:30', 'unique:option_groups,code'],
            'name' => ['required', 'string', 'max:100'],
            'selection_type' => ['required', 'string', 'in:' . implode(',', SelectionType::values())],
            'is_required' => ['required', 'boolean'],
            'min_select' => ['required', 'integer', 'min:0'],
            'max_select' => ['nullable', 'integer', 'min:1', 'gte:min_select'],
            'display_order' => ['required', 'integer', 'min:0'],
        ];
    }
}

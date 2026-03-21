<?php

namespace App\Http\Requests;

use App\Common\Constants\SelectionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOptionGroupRequest extends FormRequest
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
            'code' => ['sometimes', 'string', 'max:30', 'unique:option_groups,code,' . $this->route('id')],
            'name' => ['sometimes', 'string', 'max:100'],
            'selection_type' => ['sometimes', 'string', 'in:' . implode(',', SelectionType::values())],
            'is_required' => ['sometimes', 'boolean'],
            'min_select' => ['sometimes', 'integer', 'min:0'],
            'max_select' => ['sometimes', 'nullable', 'integer', 'min:1', 'gte:min_select'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}

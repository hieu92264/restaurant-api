<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuCategoryRequest extends FormRequest
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
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:menu_categories,id'],
            'item_type_id' => ['sometimes', 'integer', 'exists:item_types,id'],
            'code' => ['sometimes', 'string', 'max:30', 'unique:menu_categories,code,' . $this->route('id')],
            'name' => ['sometimes', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}

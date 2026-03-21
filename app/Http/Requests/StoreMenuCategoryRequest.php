<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuCategoryRequest extends FormRequest
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
            'parent_id' => ['nullable', 'integer', 'exists:menu_categories,id'],
            'item_type_id' => ['required', 'integer', 'exists:item_types,id'],
            'code' => ['required', 'string', 'max:30', 'unique:menu_categories,code'],
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['required', 'integer'],
        ];
    }
}

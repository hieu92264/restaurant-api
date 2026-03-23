<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOptionValueRequest extends FormRequest
{
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
            'option_group_id' => ['required', 'integer', 'exists::option_groups,id'],
            'value_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('option_values', 'value_code')
                    ->where(fn($query) => $query->where('option_group_id', $this->input('option_group_id'))),
                'value_name' => ['required', 'string', 'max:100'],
                'price_delta' => ['nullable', 'numeric', 'min:0']
            ]
        ];
    }
}

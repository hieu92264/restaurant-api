<?php

namespace App\Http\Requests;

use App\Models\OptionValue;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOptionValueRequest extends FormRequest
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
        $optionGroupId = $this->input('option_group_id');

        if (!$optionGroupId && $this->route('id')) {
            $existing = OptionValue::query()->find($this->route('id'));
            $optionGroupId = $existing?->option_group_id;
        }
        return [
            'option_group_id' => ['sometimes', 'integer', 'exists:option_groups,id'],
            'value_code' => [
                'sometimes',
                'string',
                'max:30',
                Rule::unique('option_values', 'value_code')
                    ->ignore($this->route('id'))
                    ->where(fn($query) => $query->where('option_group_id', $optionGroupId)),
                'value_name' => ['sometimes', 'string', 'max:100'],
                'price_delta' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            ]
        ];
    }
}

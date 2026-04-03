<?php

namespace App\Http\Requests;

use App\Common\Constants\RestaurantTableStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRestaurantTableRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(RestaurantTableStatus::values())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

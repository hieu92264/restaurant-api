<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTableSessionRequest extends FormRequest
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
            'table_id' => ['integer', 'required', 'exists:restaurant_tables,id'],
            'guest_count' => ['nullable', 'integer'],
            'remark' => ['nullable', 'string'],
            'reservation_code' => ['nullable', 'string', 'exists:reservations,reservation_code'],
        ];
    }
}

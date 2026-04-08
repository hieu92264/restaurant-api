<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
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
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_phone' => ['sometimes', 'required', 'string', 'max:20'],
            'guest_count' => ['sometimes', 'required', 'integer', 'min:1'],

            'reservation_time' => ['sometimes', 'required', 'date', 'after:now'],
            'deposit_amount' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'remark' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'hold_start_time' => ['sometimes', 'nullable', 'date'],
            'hold_end_time' => ['sometimes', 'nullable', 'date', 'after:hold_start_time'],
            'table_code' => ['sometimes', 'nullable', 'string', 'exists:restaurant_tables,slug'],
            'isConfirmed' => ['sometimes', 'nullable', 'boolean'],
            'isCancelled' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}

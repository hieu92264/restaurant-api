<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationByCustomerRequest extends FormRequest
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
            'customer_name' => ['required', 'string'],
            'customer_phone' => ['required', 'string'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'reservation_time' => ['required', 'date', 'after:now'],
            'remark' => ['nullable', 'string']
        ];
    }
}

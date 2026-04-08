<?php

namespace App\Http\Requests;

use App\Common\Constants\ReservationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'guest_count' => ['required', 'integer', 'min:1'],

            'reservation_time' => ['required', 'date', 'after:now'],
            'status' => ['nullable', 'string', Rule::in(ReservationStatus::values())],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],

            'remark' => ['nullable', 'string', 'max:1000'],
            'hold_start_time' => ['nullable', 'date'],
            'hold_end_time' => ['nullable', 'date', 'after:hold_start_time'],
            'table_code' => ['nullable', 'string', 'exists:restaurant_tables,slug'],
        ];
    }
}

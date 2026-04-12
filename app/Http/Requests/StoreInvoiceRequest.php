<?php

namespace App\Http\Requests;

use App\Common\Constants\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('payment_method')) {
            $this->merge([
                'payment_method' => PaymentMethod::normalize($this->input('payment_method')),
            ]);
        }
    }

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
            'cart_order_id' => ['required', 'integer', 'exists:cart_orders,id'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'deposit_amount' => ['sometimes', 'integer', 'min:0'],
            'paid_amount' => ['sometimes', 'integer', 'min:0'],
            'payment_method' => ['sometimes', 'nullable', 'string', Rule::in(PaymentMethod::values())],
            'issued_at' => ['sometimes', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}

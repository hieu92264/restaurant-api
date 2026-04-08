<?php

namespace App\Http\Requests;

use App\Common\Constants\TableSessionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableSessionRequest extends FormRequest
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
            'guest_count' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'remark' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'reservation_code' => ['sometimes', 'nullable', 'string', 'exists:reservations,reservation_code'],
            'status' => ['sometimes', 'required', 'string', Rule::in(TableSessionStatus::values())],
        ];
    }
}

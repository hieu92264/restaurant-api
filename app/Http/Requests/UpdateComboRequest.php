<?php

namespace App\Http\Requests;

use App\Common\Constants\ComboTag;
use App\Common\Constants\DayInWeek;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateComboRequest extends FormRequest
{
    private bool $hasInvalidJsonData = false;

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
            'data' => ['sometimes', 'string'],
            'name' => ['sometimes', 'string', 'max:150'],
            'remark' => ['sometimes', 'nullable', 'string'],
            'combo_image' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'combo_image_url' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'combo_price' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'tag' => ['sometimes', 'nullable', Rule::in(ComboTag::values())],
            'days_in_week' => ['sometimes', 'nullable', 'array'],
            'days_in_week.*' => ['string', Rule::in(DayInWeek::values())],
            'start_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'end_time' => ['sometimes', 'nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'end_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_at'],
            'max_use_times' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'dishes' => ['sometimes', 'nullable', 'array'],
            'dishes.*.dish_slug' => ['required_with:dishes', 'string', 'distinct', 'exists:dishes,slug'],
            'dishes.*.quantity' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'dishes.*.sort_order' => ['sometimes', 'nullable', 'integer'],
            'dishes.*.is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->input('data');

        if (is_string($payload) && $payload !== '') {
            $decodedPayload = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decodedPayload)) {
                $this->hasInvalidJsonData = true;
            } else {
                $this->merge($decodedPayload);
            }
        }

        if ($this->hasFile('combo_image_url') && ! $this->hasFile('combo_image')) {
            $this->files->set('combo_image', $this->file('combo_image_url'));
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->hasInvalidJsonData) {
            $validator->errors()->add('data', 'The data field must contain valid JSON.');
        }

        throw new HttpResponseException(
            response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}

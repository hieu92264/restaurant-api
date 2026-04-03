<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreDishRequest extends FormRequest
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
            'data' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_url' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'unit' => ['nullable', 'string', 'max:50'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:30'],
            'available_from' => ['nullable', 'date_format:H:i'],
            'available_to' => ['nullable', 'date_format:H:i'],
            'sort_order' => ['nullable', 'integer'],
            'options_json' => ['nullable', 'array'],
            'tags_json' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->input('data');

        if (is_string($payload) && $payload !== '') {
            $decodedPayload = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedPayload)) {
                $this->hasInvalidJsonData = true;
            } else {
                $this->merge($decodedPayload);
            }
        }

        if ($this->hasFile('image_url') && !$this->hasFile('image')) {
            $this->files->set('image', $this->file('image_url'));
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

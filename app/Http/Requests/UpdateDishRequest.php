<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UpdateDishRequest extends FormRequest
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
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'original_price' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'cost_price' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'image' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_url' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'string', 'max:30'],
            'available_from' => ['sometimes', 'nullable', 'date_format:H:i'],
            'available_to' => ['sometimes', 'nullable', 'date_format:H:i'],
            'sort_order' => ['sometimes', 'integer'],
            'options_json' => ['sometimes', 'nullable', 'array'],
            'tags_json' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
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

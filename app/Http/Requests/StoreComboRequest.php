<?php

namespace App\Http\Requests;

use App\Common\Constants\ComboTag;
use App\Common\Constants\DayInWeek;
use App\Models\Dish;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;
use Symfony\Component\HttpFoundation\Response;

class StoreComboRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'remark' => ['nullable', 'string'],
            'combo_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'combo_image_url' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'discount_price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'tag' => ['nullable', Rule::in(ComboTag::values())],
            'days_in_week' => ['nullable', 'array'],
            'days_in_week.*' => ['string', Rule::in(DayInWeek::values())],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'max_use_times' => ['nullable', 'integer', 'min:0'],
            'dishes' => ['nullable', 'array'],
            'dishes.*.dish_slug' => ['required_with:dishes', 'string', 'distinct', 'exists:dishes,slug'],
            'dishes.*.quantity' => ['nullable', 'numeric', 'gt:0'],
            'dishes.*.sort_order' => ['nullable', 'integer'],
            'dishes.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(ValidationValidator $validator): void
    {
        $validator->after(function (ValidationValidator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $discountPrice = $this->input('discount_price');

            if (!is_numeric($discountPrice)) {
                return;
            }

            $sellingPrice = $this->resolveSellingPriceFromItems($this->input('dishes', []));

            if ((int)$discountPrice > $sellingPrice) {
                $validator->errors()->add(
                    'discount_price',
                    'Chiết khấu phải nhỏ hơn hoặc bằng tổng giá bán của các món ăn trong combo'
                );
            }
        });
    }

    /**
     * @param array<int, array<string, mixed>>|mixed $items
     */
    private function resolveSellingPriceFromItems(mixed $items): int
    {
        if (!is_array($items) || $items === []) {
            return 0;
        }

        $activeItems = collect($items)
            ->filter(fn(mixed $item): bool => is_array($item) && ($item['is_active'] ?? true) === true)
            ->values();

        if ($activeItems->isEmpty()) {
            return 0;
        }

        $pricesBySlug = Dish::query()
            ->whereIn('slug', $activeItems->pluck('dish_slug')->filter()->all())
            ->pluck('price', 'slug');

        return (int)round($activeItems->sum(
            fn(array $item): float|int => ((int)($pricesBySlug[$item['dish_slug']] ?? 0)) * ((float)($item['quantity'] ?? 1))
        ));
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

        if ($this->hasFile('combo_image_url') && !$this->hasFile('combo_image')) {
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

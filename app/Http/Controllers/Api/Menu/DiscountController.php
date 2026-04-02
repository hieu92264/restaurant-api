<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Discount;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = Discount::with(['dishes:slug,name,image_url,unit,cost_price,original_price,price'])->get();
        return $this->success($data);
    }

    public function show(Request $request, $slug): JsonResponse
    {
        $discount = Discount::with(['dishes:slug,name,image_url,unit,cost_price,original_price,price'])
            ->where('slug', $slug)
            ->firstOrFail();
        return $this->success($discount);
    }

    public function store(StoreDiscountRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $discount = Discount::create($data);

        if (isset($data['dish_slugs'])) {
            $dishes = Dish::whereIn('slug', $data['dish_slugs'])->get();
            $discount->dishes()->sync($dishes->pluck('id'));
        }

        return $this->success(
            $discount->fresh()->load(['dishes:slug,name,image_url,unit,cost_price,original_price,price']),
            'Tạo mã giảm giá thành công.'
        );
    }

    public function update(UpdateDiscountRequest $request, $slug): JsonResponse
    {
        $discount = Discount::where('slug', $slug)->firstOrFail();
        $data = $request->validated();

        if (isset($data['name']) && $data['name'] !== $discount->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $discount->id);
        }

        $discount->update($data);

        if (isset($data['dish_slugs'])) {
            $dishes = Dish::whereIn('slug', $data['dish_slugs'])->get();
            $discount->dishes()->sync($dishes->pluck('id'));
        }

        return $this->success(
            $discount->fresh()->load(['dishes:slug,name,image_url,unit,cost_price,original_price,price']),
            'Cập nhật mã giảm giá thành công.'
        );
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'discount';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Discount::where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    public function destroy($slug): JsonResponse
    {
        $discount = Discount::where('slug', $slug)->firstOrFail();
        $discount->update(['is_active' => false]);

        return $this->success(null, 'Ẩn mã giảm giá thành công.');
    }
}

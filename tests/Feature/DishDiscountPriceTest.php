<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Dish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DishDiscountPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_discounted_price_respects_discount_time_window(): void
    {
        $dish = $this->createDish([
            'slug' => 'com-suon-nuong',
            'name' => 'Cơm sườn nướng',
            'price' => 65000,
        ]);

        $discount = Discount::query()->create([
            'is_active' => true,
            'slug' => 'giam-trua-15',
            'name' => 'Giảm trưa 15%',
            'scope' => 'dish',
            'discount_type' => 'percentage',
            'starts_at' => '2026-04-02 10:00:00',
            'ends_at' => '2026-04-02 14:00:00',
            'discount_value' => 15,
        ]);

        $discount->dishes()->sync([$dish->id]);

        $this->travelTo(now()->parse('2026-04-02 11:00:00'));
        $this->assertSame(55250.0, (float) $dish->fresh()->discounted_price);

        $this->travelTo(now()->parse('2026-04-02 15:00:00'));
        $this->assertSame(65000.0, (float) $dish->fresh()->discounted_price);
    }

    public function test_discounted_price_uses_the_largest_active_discount(): void
    {
        $dish = $this->createDish([
            'slug' => 'mi-xao-hai-san',
            'name' => 'Mì xào hải sản',
            'price' => 72000,
        ]);

        $fixedDiscount = Discount::query()->create([
            'is_active' => true,
            'slug' => 'dong-gia-10k',
            'name' => 'Đồng giá 10K',
            'scope' => 'dish',
            'discount_type' => 'fixed',
            'starts_at' => '2026-04-01 00:00:00',
            'ends_at' => '2026-04-30 23:59:59',
            'discount_value' => 10000,
        ]);

        $percentageDiscount = Discount::query()->create([
            'is_active' => true,
            'slug' => 'giam-20-phan-tram',
            'name' => 'Giảm 20%',
            'scope' => 'dish',
            'discount_type' => 'percentage',
            'starts_at' => '2026-04-01 00:00:00',
            'ends_at' => '2026-04-30 23:59:59',
            'discount_value' => 20,
        ]);

        $dish->discounts()->sync([$fixedDiscount->id, $percentageDiscount->id]);

        $this->travelTo(now()->parse('2026-04-02 12:00:00'));

        $this->assertSame(57600.0, (float) $dish->fresh()->discounted_price);
    }

    private function createDish(array $attributes): Dish
    {
        $category = Category::query()->create([
            'name' => 'Món test',
            'slug' => 'mon-test',
            'description' => 'Danh mục test',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return Dish::query()->create(array_merge([
            'category_id' => $category->id,
            'description' => 'Mô tả món test',
            'price' => 50000,
            'original_price' => null,
            'cost_price' => 25000,
            'unit' => 'phần',
            'is_featured' => false,
            'published_at' => null,
            'status' => 'active',
            'available_from' => '06:00',
            'available_to' => '22:00',
            'sort_order' => 1,
            'options_json' => null,
            'tags_json' => null,
            'is_active' => true,
        ], $attributes));
    }
}

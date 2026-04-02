<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Dish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'name' => 'Giảm trưa 15%',
                'description' => 'Áp dụng cho món chính trong khung giờ trưa.',
                'scope' => 'dish',
                'discount_type' => 'percentage',
                'starts_at' => now()->startOfDay()->addHours(10),
                'ends_at' => now()->startOfDay()->addHours(14),
                'quantity' => 100,
                'max_use_times' => 1,
                'discount_value' => 15,
                'min_order_value' => null,
                'sort_order' => 1,
                'dish_slugs' => ['com-suon-nuong', 'bun-bo-hue'],
            ],
            [
                'name' => 'Đồng giá topping 10K',
                'description' => 'Ưu đãi cho nhóm đồ uống bán chạy.',
                'scope' => 'dish',
                'discount_type' => 'fixed',
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(14),
                'quantity' => 200,
                'max_use_times' => 2,
                'discount_value' => 10000,
                'min_order_value' => 50000,
                'sort_order' => 2,
                'dish_slugs' => ['tra-sua-truyen-thong', 'tra-dao-cam-sa'],
            ],
            [
                'name' => 'Món mới giảm 20%',
                'description' => 'Khuyến mãi cho món mới lên menu.',
                'scope' => 'dish',
                'discount_type' => 'percentage',
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(7),
                'quantity' => 50,
                'max_use_times' => 1,
                'discount_value' => 20,
                'min_order_value' => null,
                'sort_order' => 3,
                'dish_slugs' => ['mi-xao-hai-san', 'che-khuc-bach'],
            ],
        ];

        foreach ($discounts as $item) {
            $slug = Str::slug($item['name']);
            $dishIds = Dish::query()
                ->whereIn('slug', $item['dish_slugs'])
                ->pluck('id');

            $discount = Discount::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'is_active' => true,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'scope' => $item['scope'],
                    'discount_type' => $item['discount_type'],
                    'starts_at' => $item['starts_at'],
                    'ends_at' => $item['ends_at'],
                    'quantity' => $item['quantity'],
                    'max_use_times' => $item['max_use_times'],
                    'discount_value' => $item['discount_value'],
                    'min_order_value' => $item['min_order_value'],
                    'sort_order' => $item['sort_order'],
                ]
            );

            $discount->dishes()->sync($dishIds);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Dish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DishSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dishes = [
            [
                'category_slug' => 'mon-chinh',
                'name' => 'Cơm sườn nướng',
                'description' => 'Cơm sườn nướng than, trứng ốp la và đồ chua',
                'price' => 65000,
                'original_price' => 75000,
                'cost_price' => 32000,
                'unit' => 'phần',
                'kitchen_name' => 'Cơm sườn',
                'is_featured' => true,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '06:00',
                'available_to' => '21:00',
                'sort_order' => 1,
                'options_json' => [
                    [
                        'name' => 'Size',
                        'type' => 'single',
                        'options' => [
                            ['label' => 'Thường', 'price_delta' => 0],
                            ['label' => 'Lớn', 'price_delta' => 10000],
                        ],
                    ],
                ],
                'tags_json' => ['best_seller', 'grilled'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'mon-chinh',
                'name' => 'Bún bò Huế',
                'description' => 'Bún bò với nước dùng đậm đà, chả và thịt bò',
                'price' => 55000,
                'original_price' => null,
                'cost_price' => 26000,
                'unit' => 'tô',
                'kitchen_name' => 'Bún bò',
                'is_featured' => true,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '06:00',
                'available_to' => '13:30',
                'sort_order' => 2,
                'options_json' => null,
                'tags_json' => ['spicy'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'mon-chinh',
                'name' => 'Mì xào hải sản',
                'description' => 'Mì xào cùng tôm, mực và rau cải',
                'price' => 72000,
                'original_price' => 79000,
                'cost_price' => 36000,
                'unit' => 'phần',
                'kitchen_name' => 'Mì xào',
                'is_featured' => false,
                'is_new' => true,
                'status' => 'active',
                'available_from' => '10:00',
                'available_to' => '21:30',
                'sort_order' => 3,
                'options_json' => null,
                'tags_json' => ['new'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'tra-sua',
                'name' => 'Trà sữa truyền thống',
                'description' => 'Trà sữa vị truyền thống, thơm béo',
                'price' => 39000,
                'original_price' => 45000,
                'cost_price' => 18000,
                'unit' => 'ly',
                'kitchen_name' => 'TS truyền thống',
                'is_featured' => true,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '08:00',
                'available_to' => '22:00',
                'sort_order' => 1,
                'options_json' => [
                    [
                        'name' => 'Đường',
                        'type' => 'single',
                        'options' => [
                            ['label' => '100%', 'price_delta' => 0],
                            ['label' => '50%', 'price_delta' => 0],
                            ['label' => '0%', 'price_delta' => 0],
                        ],
                    ],
                    [
                        'name' => 'Topping',
                        'type' => 'multiple',
                        'options' => [
                            ['label' => 'Trân châu đen', 'price_delta' => 8000],
                            ['label' => 'Pudding', 'price_delta' => 10000],
                        ],
                    ],
                ],
                'tags_json' => ['best_seller', 'milk_tea'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'tra-sua',
                'name' => 'Trà đào cam sả',
                'description' => 'Trà đào thanh mát cùng cam lát và sả',
                'price' => 45000,
                'original_price' => null,
                'cost_price' => 19000,
                'unit' => 'ly',
                'kitchen_name' => 'Trà đào',
                'is_featured' => true,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '08:00',
                'available_to' => '22:00',
                'sort_order' => 2,
                'options_json' => null,
                'tags_json' => ['refreshing'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'ca-phe',
                'name' => 'Cà phê sữa đá',
                'description' => 'Cà phê phin truyền thống với sữa đặc',
                'price' => 29000,
                'original_price' => null,
                'cost_price' => 12000,
                'unit' => 'ly',
                'kitchen_name' => 'Cà phê sữa',
                'is_featured' => false,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '06:00',
                'available_to' => '22:00',
                'sort_order' => 1,
                'options_json' => null,
                'tags_json' => ['coffee'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'ca-phe',
                'name' => 'Bạc xỉu',
                'description' => 'Thức uống sữa nhiều, cà phê nhẹ',
                'price' => 32000,
                'original_price' => null,
                'cost_price' => 14000,
                'unit' => 'ly',
                'kitchen_name' => 'Bạc xỉu',
                'is_featured' => false,
                'is_new' => true,
                'status' => 'active',
                'available_from' => '06:00',
                'available_to' => '22:00',
                'sort_order' => 2,
                'options_json' => null,
                'tags_json' => ['coffee', 'new'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'trang-mieng',
                'name' => 'Bánh flan',
                'description' => 'Bánh flan mềm mịn, thơm mùi trứng sữa',
                'price' => 22000,
                'original_price' => null,
                'cost_price' => 9000,
                'unit' => 'phần',
                'kitchen_name' => 'Flan',
                'is_featured' => false,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '10:00',
                'available_to' => '22:00',
                'sort_order' => 1,
                'options_json' => null,
                'tags_json' => ['dessert'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'trang-mieng',
                'name' => 'Chè khúc bạch',
                'description' => 'Chè thanh mát với khúc bạch và nhãn',
                'price' => 30000,
                'original_price' => 35000,
                'cost_price' => 14000,
                'unit' => 'chén',
                'kitchen_name' => 'Khúc bạch',
                'is_featured' => true,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '11:00',
                'available_to' => '22:00',
                'sort_order' => 2,
                'options_json' => null,
                'tags_json' => ['dessert', 'best_seller'],
                'is_active' => true,
            ],
            [
                'category_slug' => 'an-vat',
                'name' => 'Khoai tây chiên',
                'description' => 'Khoai tây giòn, ăn kèm tương ớt và sốt mayo',
                'price' => 35000,
                'original_price' => null,
                'cost_price' => 16000,
                'unit' => 'phần',
                'kitchen_name' => 'Khoai tây',
                'is_featured' => false,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '09:00',
                'available_to' => '22:00',
                'sort_order' => 1,
                'options_json' => null,
                'tags_json' => ['snack'],
                'is_active' => true,
            ],
        ];

        foreach ($dishes as $item) {
            $categoryId = Category::withoutGlobalScopes()
                ->where('slug', $item['category_slug'])
                ->value('id');

            Dish::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $this->makeSlug($item['name'])],
                [
                    'category_id' => $categoryId,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'original_price' => $item['original_price'],
                    'cost_price' => $item['cost_price'],
                    'image_url' => null,
                    'unit' => $item['unit'],
                    'kitchen_name' => $item['kitchen_name'],
                    'is_featured' => $item['is_featured'],
                    'is_new' => $item['is_new'],
                    'status' => $item['status'],
                    'available_from' => $item['available_from'],
                    'available_to' => $item['available_to'],
                    'sort_order' => $item['sort_order'],
                    'options_json' => $item['options_json'],
                    'tags_json' => $item['tags_json'],
                    'is_active' => $item['is_active'],
                ]
            );
        }
    }

    private function makeSlug(string $name): string
    {
        return Str::slug($name);
    }
}

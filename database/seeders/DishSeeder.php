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
                'category_code' => 'mon_chinh',
                'name' => 'Cơm sườn nướng',
                'description' => 'Cơm sườn nướng than, trứng ốp la và đồ chua',
                'price' => 65000,
                'original_price' => 75000,
                'cost_price' => 32000,
                'unit' => 'phan',
                'kitchen_name' => 'Com suon',
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
                            ['label' => 'Thuong', 'price_delta' => 0],
                            ['label' => 'Lon', 'price_delta' => 10000],
                        ],
                    ],
                ],
                'tags_json' => ['best_seller', 'grilled'],
                'is_active' => true,
            ],
            [
                'category_code' => 'mon_chinh',
                'name' => 'Bún bò Huế',
                'description' => 'Bún bò với nước dùng đậm đà, chả và thịt bò',
                'price' => 55000,
                'original_price' => null,
                'cost_price' => 26000,
                'unit' => 'to',
                'kitchen_name' => 'Bun bo',
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
                'category_code' => 'mon_chinh',
                'name' => 'Mì xào hải sản',
                'description' => 'Mì xào cùng tôm, mực và rau cải',
                'price' => 72000,
                'original_price' => 79000,
                'cost_price' => 36000,
                'unit' => 'phan',
                'kitchen_name' => 'Mi xao',
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
                'category_code' => 'tra_sua',
                'name' => 'Trà sữa truyền thống',
                'description' => 'Trà sữa vị truyền thống, thơm béo',
                'price' => 39000,
                'original_price' => 45000,
                'cost_price' => 18000,
                'unit' => 'ly',
                'kitchen_name' => 'TS truyen thong',
                'is_featured' => true,
                'is_new' => false,
                'status' => 'active',
                'available_from' => '08:00',
                'available_to' => '22:00',
                'sort_order' => 1,
                'options_json' => [
                    [
                        'name' => 'Duong',
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
                            ['label' => 'Tran chau den', 'price_delta' => 8000],
                            ['label' => 'Pudding', 'price_delta' => 10000],
                        ],
                    ],
                ],
                'tags_json' => ['best_seller', 'milk_tea'],
                'is_active' => true,
            ],
            [
                'category_code' => 'tra_sua',
                'name' => 'Trà đào cam sả',
                'description' => 'Trà đào thanh mát cùng cam lát và sả',
                'price' => 45000,
                'original_price' => null,
                'cost_price' => 19000,
                'unit' => 'ly',
                'kitchen_name' => 'Tra dao',
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
                'category_code' => 'ca_phe',
                'name' => 'Cà phê sữa đá',
                'description' => 'Cà phê phin truyền thống với sữa đặc',
                'price' => 29000,
                'original_price' => null,
                'cost_price' => 12000,
                'unit' => 'ly',
                'kitchen_name' => 'Cafe sua',
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
                'category_code' => 'ca_phe',
                'name' => 'Bạc xỉu',
                'description' => 'Thức uống sữa nhiều, cà phê nhẹ',
                'price' => 32000,
                'original_price' => null,
                'cost_price' => 14000,
                'unit' => 'ly',
                'kitchen_name' => 'Bac xiu',
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
                'category_code' => 'trang_mieng',
                'name' => 'Bánh flan',
                'description' => 'Bánh flan mềm mịn, thơm mùi trứng sữa',
                'price' => 22000,
                'original_price' => null,
                'cost_price' => 9000,
                'unit' => 'phan',
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
                'category_code' => 'trang_mieng',
                'name' => 'Chè khúc bạch',
                'description' => 'Chè thanh mát với khúc bạch và nhãn',
                'price' => 30000,
                'original_price' => 35000,
                'cost_price' => 14000,
                'unit' => 'chen',
                'kitchen_name' => 'Khuc bach',
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
                'category_code' => 'an_vat',
                'name' => 'Khoai tây chiên',
                'description' => 'Khoai tây giòn, ăn kèm tương ớt và sốt mayo',
                'price' => 35000,
                'original_price' => null,
                'cost_price' => 16000,
                'unit' => 'phan',
                'kitchen_name' => 'Khoai tay',
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
                ->where('code', $item['category_code'])
                ->value('id');

            Dish::withoutGlobalScopes()->updateOrCreate(
                ['code' => $this->makeCode($item['name'])],
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

    private function makeCode(string $name): string
    {
        return Str::of(Str::ascii($name))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();
    }
}

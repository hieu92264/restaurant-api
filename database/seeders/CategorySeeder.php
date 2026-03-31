<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'parent_code' => null,
                'name' => 'Món chính',
                'description' => 'Các món ăn chính bán trong ngày',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'parent_code' => null,
                'name' => 'Đồ uống',
                'description' => 'Các loại nước uống của quán',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'parent_code' => 'do_uong',
                'name' => 'Trà sữa',
                'description' => 'Nhóm trà sữa và đồ uống có sữa',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'parent_code' => 'do_uong',
                'name' => 'Cà phê',
                'description' => 'Nhóm cà phê nóng và lạnh',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'parent_code' => null,
                'name' => 'Tráng miệng',
                'description' => 'Món ngọt và món ăn sau bữa chính',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'parent_code' => null,
                'name' => 'Ăn vặt',
                'description' => 'Các món ăn nhanh và khai vị',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $item) {
            $code = $this->makeCode($item['name']);
            $parentId = null;

            if ($item['parent_code']) {
                $parentId = Category::withoutGlobalScopes()
                    ->where('code', $item['parent_code'])
                    ->value('id');
            }

            Category::withoutGlobalScopes()->updateOrCreate(
                ['code' => $code],
                [
                    'parent_id' => $parentId,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'sort_order' => $item['sort_order'],
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

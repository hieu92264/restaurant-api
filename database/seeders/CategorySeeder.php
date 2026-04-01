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
                'parent_slug' => null,
                'name' => 'Món chính',
                'description' => 'Các món ăn chính bán trong ngày',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'parent_slug' => null,
                'name' => 'Đồ uống',
                'description' => 'Các loại nước uống của quán',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'parent_slug' => 'do-uong',
                'name' => 'Trà sữa',
                'description' => 'Nhóm trà sữa và đồ uống có sữa',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'parent_slug' => 'do-uong',
                'name' => 'Cà phê',
                'description' => 'Nhóm cà phê nóng và lạnh',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'parent_slug' => null,
                'name' => 'Tráng miệng',
                'description' => 'Món ngọt và món ăn sau bữa chính',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'parent_slug' => null,
                'name' => 'Ăn vặt',
                'description' => 'Các món ăn nhanh và khai vị',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $item) {
            $slug = $this->makeSlug($item['name']);
            $parentId = null;

            if ($item['parent_slug']) {
                $parentId = Category::withoutGlobalScopes()
                    ->where('slug', $item['parent_slug'])
                    ->value('id');
            }

            Category::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $slug],
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

    private function makeSlug(string $name): string
    {
        return Str::slug($name);
    }
}

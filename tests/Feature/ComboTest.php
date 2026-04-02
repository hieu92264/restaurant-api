<?php

namespace Tests\Feature;

use App\Http\Requests\StoreComboRequest;
use App\Models\Category;
use App\Models\Combo;
use App\Models\Dish;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ComboTest extends TestCase
{
    use RefreshDatabase;

    public function test_combos_and_pivot_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('combos'));
        $this->assertTrue(Schema::hasTable('combo_dishes'));

        $this->assertTrue(Schema::hasColumns('combos', [
            'id',
            'slug',
            'name',
            'remark',
            'base_price',
            'is_active',
            'start_at',
            'end_at',
            'max_use_times',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('combo_dishes', [
            'id',
            'combo_id',
            'dish_id',
            'quantity',
            'sort_order',
            'is_active',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_store_combo_request_requires_valid_dish_slug_when_dishes_are_present(): void
    {
        $validator = Validator::make(
            [
                'name' => 'Combo trưa',
                'base_price' => 99000,
                'dishes' => [
                    [
                        'quantity' => 1,
                    ],
                ],
            ],
            (new StoreComboRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dishes.0.dish_slug', $validator->errors()->toArray());
    }

    public function test_authenticated_user_can_store_combo_with_dishes(): void
    {
        $user = $this->createAuthenticatedUser();
        [$dishOne, $dishTwo] = $this->createSampleDishes();

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/menu/combos', [
            'name' => 'Combo trưa văn phòng',
            'remark' => 'Combo cơ bản cho 2 người',
            'base_price' => 129000,
            'is_active' => true,
            'start_at' => now()->toDateTimeString(),
            'end_at' => now()->addDays(7)->toDateTimeString(),
            'max_use_times' => 50,
            'dishes' => [
                [
                    'dish_slug' => $dishOne->slug,
                    'quantity' => 1,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'dish_slug' => $dishTwo->slug,
                    'quantity' => 2,
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.name', 'Combo trưa văn phòng')
            ->assertJsonPath('metadata.base_price', '129000.00');

        $combo = Combo::query()->where('slug', 'combo-trua-van-phong')->first();

        $this->assertNotNull($combo);
        $this->assertDatabaseHas('combo_dishes', [
            'combo_id' => $combo->id,
            'dish_id' => $dishOne->id,
            'quantity' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('combo_dishes', [
            'combo_id' => $combo->id,
            'dish_id' => $dishTwo->id,
            'quantity' => 2,
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_update_combo_and_resync_dishes(): void
    {
        $user = $this->createAuthenticatedUser();
        [$dishOne, $dishTwo, $dishThree] = $this->createSampleDishes(3);

        $combo = Combo::query()->create([
            'slug' => 'combo-sang',
            'name' => 'Combo sáng',
            'remark' => 'Combo buổi sáng',
            'base_price' => 89000,
            'is_active' => true,
            'max_use_times' => 10,
        ]);

        $combo->dishes()->sync([
            $dishOne->id => ['quantity' => 1, 'sort_order' => 1, 'is_active' => true],
            $dishTwo->id => ['quantity' => 1, 'sort_order' => 2, 'is_active' => true],
        ]);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/menu/combos/combo-sang', [
            'name' => 'Combo sáng đặc biệt',
            'base_price' => 99000,
            'dishes' => [
                [
                    'dish_slug' => $dishThree->slug,
                    'quantity' => 3,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Combo sáng đặc biệt')
            ->assertJsonPath('metadata.base_price', '99000.00');

        $combo->refresh();

        $this->assertSame('combo-sang-dac-biet', $combo->slug);
        $this->assertSame('Combo sáng đặc biệt', $combo->name);
        $this->assertSame('99000.00', $combo->base_price);

        $this->assertDatabaseHas('combo_dishes', [
            'combo_id' => $combo->id,
            'dish_id' => $dishThree->id,
            'quantity' => 3,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $this->assertDatabaseMissing('combo_dishes', [
            'combo_id' => $combo->id,
            'dish_id' => $dishOne->id,
        ]);
        $this->assertDatabaseMissing('combo_dishes', [
            'combo_id' => $combo->id,
            'dish_id' => $dishTwo->id,
        ]);
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Administrator',
            'code' => 'ADMIN',
            'remark' => 'Test admin role',
        ]);

        return User::query()->create([
            'is_active' => true,
            'user_name' => 'combo.admin',
            'full_name' => 'Combo Admin',
            'email' => 'combo-admin@example.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }

    /**
     * @return array<int, Dish>
     */
    private function createSampleDishes(int $count = 2): array
    {
        $category = Category::query()->create([
            'name' => 'Danh mục combo test',
            'slug' => 'danh-muc-combo-test',
            'description' => 'Danh mục dùng cho test combo',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $definitions = [
            ['slug' => 'combo-mon-1', 'name' => 'Combo món 1'],
            ['slug' => 'combo-mon-2', 'name' => 'Combo món 2'],
            ['slug' => 'combo-mon-3', 'name' => 'Combo món 3'],
        ];

        $dishes = [];

        foreach (array_slice($definitions, 0, $count) as $index => $definition) {
            $dishes[] = Dish::query()->create([
                'category_id' => $category->id,
                'slug' => $definition['slug'],
                'name' => $definition['name'],
                'description' => 'Mô tả món test cho combo',
                'price' => 45000 + ($index * 5000),
                'original_price' => 52000 + ($index * 5000),
                'cost_price' => 22000 + ($index * 2000),
                'unit' => 'phần',
                'is_featured' => false,
                'published_at' => null,
                'status' => 'active',
                'available_from' => '06:00',
                'available_to' => '22:00',
                'sort_order' => $index + 1,
                'options_json' => null,
                'tags_json' => null,
                'is_active' => true,
            ]);
        }

        return $dishes;
    }
}

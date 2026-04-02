<?php

namespace Tests\Feature;

use App\Http\Requests\StoreDiscountRequest;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Dish;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_discounts_and_pivot_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('discounts'));
        $this->assertTrue(Schema::hasTable('dish_discount'));

        $this->assertTrue(Schema::hasColumns('discounts', [
            'id',
            'is_active',
            'slug',
            'name',
            'description',
            'scope',
            'discount_type',
            'starts_at',
            'ends_at',
            'quantity',
            'max_use_times',
            'discount_value',
            'min_order_value',
            'sort_order',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('dish_discount', [
            'id',
            'dish_id',
            'discount_id',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_store_discount_request_requires_dish_slugs_for_dish_scope(): void
    {
        $validator = Validator::make(
            [
                'name' => 'Giảm món mới',
                'scope' => 'dish',
                'discount_type' => 'percentage',
                'discount_value' => 10,
            ],
            (new StoreDiscountRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dish_slugs', $validator->errors()->toArray());
    }

    public function test_authenticated_user_can_store_discount_for_dishes(): void
    {
        $user = $this->createAuthenticatedUser();
        [$dishOne, $dishTwo] = $this->createSampleDishes();

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/menu/discounts', [
            'name' => 'Giảm sáng 10%',
            'description' => 'Ưu đãi áp dụng cho món ăn sáng.',
            'scope' => 'dish',
            'discount_type' => 'percentage',
            'starts_at' => now()->toDateTimeString(),
            'ends_at' => now()->addDays(5)->toDateTimeString(),
            'quantity' => 30,
            'max_use_times' => 1,
            'discount_value' => 10,
            'min_order_value' => 50000,
            'sort_order' => 1,
            'dish_slugs' => [$dishOne->slug, $dishTwo->slug],
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Giảm sáng 10%')
            ->assertJsonPath('metadata.scope', 'dish');

        $discount = Discount::query()->where('slug', 'giam-sang-10')->first();

        $this->assertNotNull($discount);
        $this->assertDatabaseHas('dish_discount', [
            'discount_id' => $discount->id,
            'dish_id' => $dishOne->id,
        ]);
        $this->assertDatabaseHas('dish_discount', [
            'discount_id' => $discount->id,
            'dish_id' => $dishTwo->id,
        ]);
    }

    public function test_authenticated_user_can_update_discount_and_resync_dishes(): void
    {
        $user = $this->createAuthenticatedUser();
        [$dishOne, $dishTwo, $dishThree] = $this->createSampleDishes(3);

        $discount = Discount::query()->create([
            'is_active' => true,
            'slug' => 'giam-combo',
            'name' => 'Giảm combo',
            'description' => 'Ưu đãi ban đầu',
            'scope' => 'dish',
            'discount_type' => 'fixed',
            'discount_value' => 15000,
            'sort_order' => 1,
        ]);

        $discount->dishes()->sync([$dishOne->id, $dishTwo->id]);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/menu/discounts/giam-combo', [
            'name' => 'Giảm combo mới',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'dish_slugs' => [$dishThree->slug],
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Giảm combo mới')
            ->assertJsonPath('metadata.discount_type', 'percentage');

        $discount->refresh();

        $this->assertSame('giam-combo-moi', $discount->slug);
        $this->assertSame('Giảm combo mới', $discount->name);
        $this->assertSame('percentage', $discount->discount_type);
        $this->assertSame('20', (string) $discount->discount_value);

        $this->assertDatabaseHas('dish_discount', [
            'discount_id' => $discount->id,
            'dish_id' => $dishThree->id,
        ]);
        $this->assertDatabaseMissing('dish_discount', [
            'discount_id' => $discount->id,
            'dish_id' => $dishOne->id,
        ]);
        $this->assertDatabaseMissing('dish_discount', [
            'discount_id' => $discount->id,
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
            'user_name' => 'admin.test',
            'full_name' => 'Admin Test',
            'email' => 'admin@example.com',
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
            'name' => 'Món test',
            'slug' => 'mon-test',
            'description' => 'Danh mục test',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $definitions = [
            ['slug' => 'mon-test-1', 'name' => 'Món test 1'],
            ['slug' => 'mon-test-2', 'name' => 'Món test 2'],
            ['slug' => 'mon-test-3', 'name' => 'Món test 3'],
        ];

        $dishes = [];

        foreach (array_slice($definitions, 0, $count) as $index => $definition) {
            $dishes[] = Dish::query()->create([
                'category_id' => $category->id,
                'slug' => $definition['slug'],
                'name' => $definition['name'],
                'description' => 'Mô tả món test',
                'price' => 50000 + ($index * 5000),
                'original_price' => 60000 + ($index * 5000),
                'cost_price' => 25000 + ($index * 3000),
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

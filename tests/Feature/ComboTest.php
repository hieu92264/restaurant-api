<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Combo;
use App\Models\Dish;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
            'combo_image',
            'is_active',
            'discount_price',
            'max_use_times',
            'tag',
            'days_in_week',
            'start_time',
            'end_time',
            'start_at',
            'end_at',
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
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'api')->post('/api/v1/menu/combos', [
            'data' => json_encode([
                'name' => 'Combo trua',
                'discount_price' => 10000,
                'dishes' => [
                    [
                        'quantity' => 1,
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['dishes.0.dish_slug']);
    }

    public function test_authenticated_user_can_store_combo_with_multipart_data_and_image_file(): void
    {
        Storage::fake('public');

        $user = $this->createAuthenticatedUser();
        [$dishOne, $dishTwo] = $this->createSampleDishes();

        $response = $this->actingAs($user, 'api')->post('/api/v1/menu/combos', [
            'combo_image' => UploadedFile::fake()->image('combo.png'),
            'data' => json_encode([
                'name' => 'Combo trua van phong',
                'remark' => 'Combo co ban cho 2 nguoi',
                'discount_price' => 16000,
                'is_active' => true,
                'tag' => 'HOT',
                'days_in_week' => ['T2', 'T3'],
                'start_time' => '10:00',
                'end_time' => '14:00',
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
            ], JSON_THROW_ON_ERROR),
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.name', 'Combo trua van phong')
            ->assertJsonPath('metadata.discount_price', 16000)
            ->assertJsonPath('metadata.selling_price', 145000)
            ->assertJsonPath('metadata.combo_price', 129000)
            ->assertJsonPath('metadata.tag', 'HOT')
            ->assertJsonPath('metadata.days_in_week.0', 'T2')
            ->assertJsonPath('metadata.combo_image.name', fn (?string $value) => is_string($value) && str_ends_with($value, '.webp'))
            ->assertJsonPath('metadata.combo_image.url', fn (?string $value) => is_string($value) && str_starts_with($value, 'storage/combos/'));

        $combo = Combo::query()->where('slug', 'combo-trua-van-phong')->first();

        $this->assertNotNull($combo);
        $this->assertSame(16000, $combo->discount_price);
        $this->assertSame(145000, $combo->selling_price);
        $this->assertSame(129000, $combo->combo_price);
        $this->assertSame(['T2', 'T3'], $combo->days_in_week);
        $this->assertNotNull($combo->combo_image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $combo->combo_image['url']));
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

    public function test_authenticated_user_can_update_combo_with_multipart_data_and_image_file(): void
    {
        Storage::fake('public');

        $user = $this->createAuthenticatedUser();
        [$dishOne, $dishTwo, $dishThree] = $this->createSampleDishes(3);

        $combo = Combo::query()->create([
            'slug' => 'combo-sang',
            'name' => 'Combo sang',
            'remark' => 'Combo buoi sang',
            'combo_image' => null,
            'discount_price' => 1000,
            'is_active' => true,
            'max_use_times' => 10,
        ]);

        $combo->dishes()->sync([
            $dishOne->id => ['quantity' => 1, 'sort_order' => 1, 'is_active' => true],
            $dishTwo->id => ['quantity' => 1, 'sort_order' => 2, 'is_active' => true],
        ]);

        $response = $this->actingAs($user, 'api')->post('/api/v1/menu/combos/combo-sang', [
            '_method' => 'PATCH',
            'combo_image' => UploadedFile::fake()->image('combo-update.png'),
            'data' => json_encode([
                'name' => 'Combo sang dac biet',
                'discount_price' => 66000,
                'tag' => 'FAST',
                'days_in_week' => ['T6'],
                'dishes' => [
                    [
                        'dish_slug' => $dishThree->slug,
                        'quantity' => 3,
                        'sort_order' => 1,
                        'is_active' => true,
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Combo sang dac biet')
            ->assertJsonPath('metadata.discount_price', 66000)
            ->assertJsonPath('metadata.selling_price', 165000)
            ->assertJsonPath('metadata.combo_price', 99000)
            ->assertJsonPath('metadata.tag', 'FAST')
            ->assertJsonPath('metadata.days_in_week.0', 'T6')
            ->assertJsonPath('metadata.combo_image.url', fn (?string $value) => is_string($value) && str_starts_with($value, 'storage/combos/'));

        $combo->refresh();

        $this->assertSame('combo-sang-dac-biet', $combo->slug);
        $this->assertSame('Combo sang dac biet', $combo->name);
        $this->assertSame(66000, $combo->discount_price);
        $this->assertSame(165000, $combo->selling_price);
        $this->assertSame(99000, $combo->combo_price);
        $this->assertSame(['T6'], $combo->days_in_week);
        $this->assertNotNull($combo->combo_image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $combo->combo_image['url']));

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

    public function test_store_combo_request_rejects_invalid_json_data_field(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'api')->post('/api/v1/menu/combos', [
            'data' => '{"name":"Combo trua",',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['data']);
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
            'name' => 'Danh muc combo test',
            'slug' => 'danh-muc-combo-test',
            'description' => 'Danh muc dung cho test combo',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $definitions = [
            ['slug' => 'combo-mon-1', 'name' => 'Combo mon 1'],
            ['slug' => 'combo-mon-2', 'name' => 'Combo mon 2'],
            ['slug' => 'combo-mon-3', 'name' => 'Combo mon 3'],
        ];

        $dishes = [];

        foreach (array_slice($definitions, 0, $count) as $index => $definition) {
            $dishes[] = Dish::query()->create([
                'category_id' => $category->id,
                'slug' => $definition['slug'],
                'name' => $definition['name'],
                'description' => 'Mo ta mon test cho combo',
                'price' => 45000 + ($index * 5000),
                'original_price' => 52000 + ($index * 5000),
                'cost_price' => 22000 + ($index * 2000),
                'unit' => 'phan',
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

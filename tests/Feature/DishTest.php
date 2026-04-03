<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DishTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_dish_with_multipart_data_and_image_file(): void
    {
        Storage::fake('public');

        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $response = $this->actingAs($user, 'api')->post('/api/v1/menu/dishes', [
            'image' => UploadedFile::fake()->image('pho.png'),
            'data' => json_encode([
                'category_id' => $category->id,
                'name' => 'Pho bo dac biet',
                'description' => 'Mon nuoc noi bat',
                'price' => 65000,
                'original_price' => 72000,
                'cost_price' => 30000,
                'unit' => 'to',
                'is_featured' => true,
                'status' => 'active',
                'available_from' => '06:00',
                'available_to' => '22:00',
                'sort_order' => 1,
                'options_json' => [['name' => 'Them trung', 'price' => 10000]],
                'tags_json' => ['best-seller', 'breakfast'],
                'is_active' => true,
            ], JSON_THROW_ON_ERROR),
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.name', 'Pho bo dac biet')
            ->assertJsonPath('metadata.image.name', fn (?string $value) => is_string($value) && str_ends_with($value, '.webp'))
            ->assertJsonPath('metadata.image.url', fn (?string $value) => is_string($value) && str_starts_with($value, 'storage/dishes/'))
            ->assertJsonPath('metadata.image.size', fn ($value) => is_int($value) && $value > 0)
            ->assertJsonPath('metadata.options_json.0.name', 'Them trung')
            ->assertJsonPath('metadata.tags_json.0', 'best-seller');

        $dish = Dish::query()->where('slug', 'pho-bo-dac-biet')->first();

        $this->assertNotNull($dish);
        $this->assertNotNull($dish->image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $dish->image['url']));
    }

    public function test_authenticated_user_can_update_dish_with_multipart_data_and_image_file(): void
    {
        Storage::fake('public');

        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $dish = Dish::query()->create([
            'category_id' => $category->id,
            'slug' => 'bun-bo-hue',
            'name' => 'Bun bo Hue',
            'description' => 'Mo ta cu',
            'price' => 55000,
            'original_price' => 60000,
            'cost_price' => 28000,
            'image' => null,
            'unit' => 'to',
            'is_featured' => false,
            'published_at' => null,
            'status' => 'active',
            'available_from' => '06:00',
            'available_to' => '22:00',
            'sort_order' => 1,
            'options_json' => null,
            'tags_json' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'api')->post('/api/v1/menu/dishes/bun-bo-hue', [
            '_method' => 'PUT',
            'image' => UploadedFile::fake()->image('bun-bo.png'),
            'data' => json_encode([
                'category_id' => $category->id,
                'name' => 'Bun bo Hue dac biet',
                'description' => 'Mo ta moi',
                'price' => 69000,
                'original_price' => 76000,
                'cost_price' => 32000,
                'unit' => 'to',
                'is_featured' => true,
                'is_new' => true,
                'status' => 'active',
                'available_from' => '07:00',
                'available_to' => '21:00',
                'sort_order' => 2,
                'options_json' => [['name' => 'Them cha', 'price' => 12000]],
                'tags_json' => ['signature'],
                'is_active' => true,
            ], JSON_THROW_ON_ERROR),
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Bun bo Hue dac biet')
            ->assertJsonPath('metadata.slug', 'bun-bo-hue-dac-biet')
            ->assertJsonPath('metadata.tags_json.0', 'signature')
            ->assertJsonPath('metadata.image.url', fn (?string $value) => is_string($value) && str_starts_with($value, 'storage/dishes/'));

        $dish->refresh();

        $this->assertSame('bun-bo-hue-dac-biet', $dish->slug);
        $this->assertSame(69000, $dish->price);
        $this->assertSame(76000, $dish->original_price);
        $this->assertSame(32000, $dish->cost_price);
        $this->assertSame('Mo ta moi', $dish->description);
        $this->assertSame('07:00', $dish->available_from);
        $this->assertSame('21:00', $dish->available_to);
        $this->assertSame(2, $dish->sort_order);
        $this->assertTrue($dish->is_featured);
        $this->assertNotNull($dish->published_at);
        $this->assertNotNull($dish->image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $dish->image['url']));
    }

    public function test_store_dish_request_rejects_invalid_json_data_field(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'api')->post('/api/v1/menu/dishes', [
            'data' => '{"name":"Pho bo",',
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
            'user_name' => 'dish.admin',
            'full_name' => 'Dish Admin',
            'email' => 'dish-admin@example.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }

    private function createCategory(): Category
    {
        return Category::query()->create([
            'name' => 'Mon nuoc',
            'slug' => 'mon-nuoc',
            'description' => 'Danh muc mon nuoc',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}

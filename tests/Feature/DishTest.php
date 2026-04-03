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
            'image' => UploadedFile::fake()->image('bun-bo.png'),
            'data' => json_encode([
                'name' => 'Bun bo Hue dac biet',
                'price' => 69000,
                'is_new' => true,
                'tags_json' => ['signature'],
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
        $this->assertNotNull($dish->published_at);
        $this->assertNotNull($dish->image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $dish->image['url']));
    }

    public function test_authenticated_user_can_update_dish_with_patch_json_payload(): void
    {
        $user = $this->createAuthenticatedUser();
        $category = $this->createCategory();

        $dish = Dish::query()->create([
            'category_id' => $category->id,
            'slug' => 'com-tam',
            'name' => 'Com tam',
            'description' => 'Mon cu',
            'price' => 45000,
            'original_price' => 50000,
            'cost_price' => 22000,
            'image' => null,
            'unit' => 'phan',
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

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/menu/dishes/com-tam', [
            'name' => 'Com tam suon',
            'price' => 49000,
            'is_featured' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Com tam suon')
            ->assertJsonPath('metadata.slug', 'com-tam-suon')
            ->assertJsonPath('metadata.price', 49000);

        $dish->refresh();

        $this->assertSame('com-tam-suon', $dish->slug);
        $this->assertSame(49000, $dish->price);
        $this->assertTrue($dish->is_featured);
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

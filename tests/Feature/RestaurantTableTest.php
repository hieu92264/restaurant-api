<?php

namespace Tests\Feature;

use App\Http\Requests\StoreRestaurantTableRequest;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RestaurantTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_tables_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('restaurant_tables'));

        $this->assertTrue(Schema::hasColumns('restaurant_tables', [
            'id',
            'slug',
            'name',
            'capacity',
            'status',
            'is_active',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_store_restaurant_table_request_requires_valid_status(): void
    {
        $validator = Validator::make(
            [
                'name' => 'Bàn VIP 1',
                'capacity' => 6,
                'status' => 'BUSY',
            ],
            (new StoreRestaurantTableRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_authenticated_user_can_store_restaurant_table(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/table/tables', [
            'name' => 'Bàn sân vườn 01',
            'capacity' => 4,
            'status' => 'AVAILABLE',
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.name', 'Bàn sân vườn 01')
            ->assertJsonPath('metadata.slug', 'ban-san-vuon-01')
            ->assertJsonPath('metadata.capacity', 4)
            ->assertJsonPath('metadata.status', 'AVAILABLE');

        $this->assertDatabaseHas('restaurant_tables', [
            'slug' => 'ban-san-vuon-01',
            'name' => 'Bàn sân vườn 01',
            'capacity' => 4,
            'status' => 'AVAILABLE',
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_update_restaurant_table(): void
    {
        $user = $this->createAuthenticatedUser();

        $table = RestaurantTable::query()->create([
            'slug' => 'ban-tang-1',
            'name' => 'Bàn tầng 1',
            'capacity' => 4,
            'status' => 'AVAILABLE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/table/tables/ban-tang-1', [
            'name' => 'Bàn tầng 1 VIP',
            'capacity' => 8,
            'status' => 'RESERVED',
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Bàn tầng 1 VIP')
            ->assertJsonPath('metadata.slug', 'ban-tang-1-vip')
            ->assertJsonPath('metadata.capacity', 8)
            ->assertJsonPath('metadata.status', 'RESERVED');

        $table->refresh();

        $this->assertSame('ban-tang-1-vip', $table->slug);
        $this->assertSame('Bàn tầng 1 VIP', $table->name);
        $this->assertSame(8, $table->capacity);
        $this->assertSame('RESERVED', $table->status);
    }

    public function test_authenticated_user_can_soft_delete_restaurant_table(): void
    {
        $user = $this->createAuthenticatedUser();

        $table = RestaurantTable::query()->create([
            'slug' => 'ban-ngoai-troi',
            'name' => 'Bàn ngoài trời',
            'capacity' => 6,
            'status' => 'AVAILABLE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'api')->deleteJson('/api/v1/table/tables/ban-ngoai-troi');

        $response->assertOk();

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'is_active' => false,
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
            'user_name' => 'table.admin',
            'full_name' => 'Table Admin',
            'email' => 'table-admin@example.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }
}

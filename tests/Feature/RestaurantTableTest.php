<?php

namespace Tests\Feature;

use App\Common\Constants\ReservationStatus;
use App\Common\Constants\RestaurantTableStatus;
use App\Common\Constants\TableSessionStatus;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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
            'sort_order',
            'is_active',
            'created_at',
            'updated_at',
        ]));

        $this->assertFalse(Schema::hasColumn('restaurant_tables', 'status'));
    }

    public function test_authenticated_user_can_store_restaurant_table(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/table/tables', [
            'name' => 'Ban san vuon 01',
            'capacity' => 4,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.name', 'Ban san vuon 01')
            ->assertJsonPath('metadata.slug', 'ban-san-vuon-01')
            ->assertJsonPath('metadata.capacity', 4)
            ->assertJsonPath('metadata.sort_order', 3)
            ->assertJsonPath('metadata.status', RestaurantTableStatus::AVAILABLE);

        $this->assertDatabaseHas('restaurant_tables', [
            'slug' => 'ban-san-vuon-01',
            'name' => 'Ban san vuon 01',
            'capacity' => 4,
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_update_restaurant_table(): void
    {
        $user = $this->createAuthenticatedUser();

        $table = RestaurantTable::query()->create([
            'slug' => 'ban-tang-1',
            'name' => 'Ban tang 1',
            'capacity' => 4,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/table/tables/ban-tang-1', [
            'name' => 'Ban tang 1 VIP',
            'capacity' => 8,
            'sort_order' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.name', 'Ban tang 1 VIP')
            ->assertJsonPath('metadata.slug', 'ban-tang-1-vip')
            ->assertJsonPath('metadata.capacity', 8)
            ->assertJsonPath('metadata.sort_order', 2)
            ->assertJsonPath('metadata.status', RestaurantTableStatus::AVAILABLE);

        $table->refresh();

        $this->assertSame('ban-tang-1-vip', $table->slug);
        $this->assertSame('Ban tang 1 VIP', $table->name);
        $this->assertSame(8, $table->capacity);
        $this->assertSame(2, $table->sort_order);
        $this->assertSame(RestaurantTableStatus::AVAILABLE, $table->status);
    }

    public function test_index_orders_restaurant_tables_by_sort_order_then_name(): void
    {
        $user = $this->createAuthenticatedUser();

        RestaurantTable::query()->create([
            'slug' => 'ban-b',
            'name' => 'Ban B',
            'capacity' => 4,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        RestaurantTable::query()->create([
            'slug' => 'ban-a',
            'name' => 'Ban A',
            'capacity' => 4,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        RestaurantTable::query()->create([
            'slug' => 'ban-c',
            'name' => 'Ban C',
            'capacity' => 4,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/table/tables')
            ->assertOk()
            ->assertJsonPath('metadata.0.slug', 'ban-a')
            ->assertJsonPath('metadata.1.slug', 'ban-b')
            ->assertJsonPath('metadata.2.slug', 'ban-c');
    }

    public function test_authenticated_user_can_soft_delete_restaurant_table(): void
    {
        $user = $this->createAuthenticatedUser();

        $table = RestaurantTable::query()->create([
            'slug' => 'ban-ngoai-troi',
            'name' => 'Ban ngoai troi',
            'capacity' => 6,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'api')
            ->deleteJson('/api/v1/table/tables/ban-ngoai-troi')
            ->assertOk();

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'is_active' => false,
        ]);
    }

    public function test_table_status_is_reserved_when_holding_reservation_exists(): void
    {
        $table = RestaurantTable::query()->create([
            'slug' => 'ban-dat-truoc',
            'name' => 'Ban dat truoc',
            'capacity' => 4,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Reservation::query()->create([
            'is_active' => true,
            'reservation_code' => 'RES-HOLDING',
            'customer_name' => 'Khach giu ban',
            'customer_phone' => '0900000001',
            'guest_count' => 4,
            'reservation_time' => now()->addMinutes(10),
            'status' => ReservationStatus::CONFIRMED,
            'deposit_amount' => 0,
            'hold_start_time' => now()->subMinutes(5),
            'hold_end_time' => now()->addMinutes(25),
            'table_code' => $table->slug,
        ]);

        $table = RestaurantTable::query()
            ->withComputedStatus()
            ->findOrFail($table->id);

        $this->assertSame(RestaurantTableStatus::RESERVED, $table->status);
    }

    public function test_table_status_prioritizes_live_session_over_reservation(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = RestaurantTable::query()->create([
            'slug' => 'ban-dang-phuc-vu',
            'name' => 'Ban dang phuc vu',
            'capacity' => 4,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Reservation::query()->create([
            'is_active' => true,
            'reservation_code' => 'RES-SESSION-FIRST',
            'customer_name' => 'Khach giu ban',
            'customer_phone' => '0900000002',
            'guest_count' => 4,
            'reservation_time' => now()->addMinutes(10),
            'status' => ReservationStatus::PENDING,
            'deposit_amount' => 0,
            'hold_start_time' => now()->subMinutes(5),
            'hold_end_time' => now()->addMinutes(25),
            'table_code' => $table->slug,
        ]);

        TableSession::query()->create([
            'table_id' => $table->id,
            'opened_by_employee' => $user->user_name,
            'guest_count' => 4,
            'status' => TableSessionStatus::OPEN,
            'opened_at' => now()->subMinutes(15),
            'remark' => 'Dang phuc vu',
            'is_active' => true,
        ]);

        $table = RestaurantTable::query()
            ->withComputedStatus()
            ->findOrFail($table->id);

        $this->assertSame(RestaurantTableStatus::OCCUPIED, $table->status);
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

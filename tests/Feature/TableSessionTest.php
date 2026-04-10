<?php

namespace Tests\Feature;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\RestaurantTableStatus;
use App\Common\Constants\TableSessionStatus;
use App\Models\CartOrder;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_table_session_for_available_table(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a01');

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/table/sessions', [
            'table_id' => $table->id,
            'guest_count' => 4,
            'remark' => 'Khach vao som',
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.table_id', $table->id)
            ->assertJsonPath('metadata.status', TableSessionStatus::OPEN)
            ->assertJsonPath('metadata.opened_by_employee', $user->user_name)
            ->assertJsonPath('metadata.guest_count', 4);

        $this->assertDatabaseHas('table_sessions', [
            'table_id' => $table->id,
            'status' => TableSessionStatus::OPEN,
            'opened_by_employee' => $user->user_name,
            'guest_count' => 4,
            'is_active' => true,
        ]);

        $this->assertSame(RestaurantTableStatus::OCCUPIED, $table->fresh()->status);
    }

    public function test_authenticated_user_cannot_store_duplicate_live_session_for_same_table(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a02');
        $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);

        $this->actingAs($user, 'api')->postJson('/api/v1/table/sessions', [
            'table_id' => $table->id,
            'guest_count' => 2,
        ])->assertBadRequest();

        $this->assertDatabaseCount('table_sessions', 1);
    }

    public function test_authenticated_user_can_close_table_session(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a03');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/table/sessions/' . $session->id, [
            'status' => TableSessionStatus::CLOSED,
            'remark' => 'Khach da roi ban',
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.status', TableSessionStatus::CLOSED)
            ->assertJsonPath('metadata.closed_by_employee', $user->user_name)
            ->assertJsonPath('metadata.remark', 'Khach da roi ban');

        $session->refresh();

        $this->assertSame(TableSessionStatus::CLOSED, $session->status);
        $this->assertSame($user->user_name, $session->closed_by_employee);
        $this->assertNotNull($session->closed_at);
        $this->assertSame(RestaurantTableStatus::AVAILABLE, $table->fresh()->status);
    }

    public function test_authenticated_user_cannot_close_table_session_when_open_cart_order_exists(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a04');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);
        $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::OPEN);

        $this->actingAs($user, 'api')->patchJson('/api/v1/table/sessions/' . $session->id, [
            'status' => TableSessionStatus::CLOSED,
        ])->assertBadRequest();

        $session->refresh();

        $this->assertSame(TableSessionStatus::OPEN, $session->status);
        $this->assertNull($session->closed_at);
    }

    public function test_authenticated_user_can_show_table_session(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a05');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/table/sessions/' . $session->id)
            ->assertOk()
            ->assertJsonPath('metadata.id', $session->id)
            ->assertJsonPath('metadata.table_id', $table->id)
            ->assertJsonPath('metadata.status', TableSessionStatus::OPEN);
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Administrator',
            'code' => 'ADMIN',
            'remark' => 'Table session test admin role',
        ]);

        return User::query()->create([
            'is_active' => true,
            'user_name' => 'table.session.admin',
            'full_name' => 'Table Session Admin',
            'email' => 'table-session-admin@example.com',
            'password' => 'password',
            'role_id' => $role->id,
        ]);
    }

    private function createTable(string $slug): RestaurantTable
    {
        return RestaurantTable::query()->create([
            'slug' => $slug,
            'name' => 'Ban ' . strtoupper($slug),
            'capacity' => 4,
            'is_active' => true,
        ]);
    }

    private function createSession(
        RestaurantTable $table,
        string $openedByEmployee,
        string $status
    ): TableSession {
        return TableSession::query()->create([
            'table_id' => $table->id,
            'opened_by_employee' => $openedByEmployee,
            'guest_count' => 4,
            'status' => $status,
            'opened_at' => now()->subHour(),
            'closed_at' => null,
            'remark' => 'Session test',
            'is_active' => true,
        ]);
    }

    private function createCartOrder(
        TableSession $session,
        RestaurantTable $table,
        string $createdByEmployee,
        string $status
    ): CartOrder {
        return CartOrder::query()->create([
            'session_id' => $session->id,
            'table_id' => $table->id,
            'order_no' => 'ORD-' . $session->id . '-' . str_replace('_', '-', $status),
            'created_by_employee' => $createdByEmployee,
            'status' => $status,
            'subtotal_amount' => 100000,
            'discount_amount' => 0,
            'service_charge_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'remark' => 'Order test',
            'is_active' => true,
        ]);
    }
}

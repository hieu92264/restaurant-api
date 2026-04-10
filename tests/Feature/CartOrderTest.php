<?php

namespace Tests\Feature;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\TableSessionStatus;
use App\Models\CartOrder;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_cart_order_for_live_session(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b01');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/table/cart-orders', [
            'session_id' => $session->id,
            'table_id' => $table->id,
            'subtotal_amount' => 120000,
            'discount_amount' => 0,
            'service_charge_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 120000,
            'remark' => 'Order moi',
            'items' => [
                [
                    'item_name_snapshot' => 'Com suon',
                    'variant_name_snapshot' => 'Lon',
                    'quantity' => 2,
                    'base_unit_price' => 60000,
                    'option_total_price' => 0,
                    'unit_final_price' => 60000,
                    'line_total' => 120000,
                    'item_note' => 'It hanh',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.session_id', $session->id)
            ->assertJsonPath('metadata.table_id', $table->id)
            ->assertJsonPath('metadata.status', CartOrderStatus::OPEN)
            ->assertJsonPath('metadata.created_by_employee', $user->user_name)
            ->assertJsonPath('metadata.items.0.item_name_snapshot', 'Com suon');

        $this->assertDatabaseHas('cart_orders', [
            'session_id' => $session->id,
            'table_id' => $table->id,
            'status' => CartOrderStatus::OPEN,
            'created_by_employee' => $user->user_name,
            'total_amount' => 120000,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('cart_order_items', [
            'item_name_snapshot' => 'Com suon',
            'line_total' => 120000,
        ]);
    }

    public function test_authenticated_user_cannot_store_cart_order_for_closed_session(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b02');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::CLOSED);

        $this->actingAs($user, 'api')->postJson('/api/v1/table/cart-orders', [
            'session_id' => $session->id,
            'table_id' => $table->id,
            'subtotal_amount' => 50000,
            'total_amount' => 50000,
            'items' => [
                [
                    'item_name_snapshot' => 'Tra dao',
                    'quantity' => 1,
                    'base_unit_price' => 50000,
                    'unit_final_price' => 50000,
                    'line_total' => 50000,
                ],
            ],
        ])->assertBadRequest();

        $this->assertDatabaseCount('cart_orders', 0);
    }

    public function test_authenticated_user_can_update_cart_order_status(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b03');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::OPEN);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/table/cart-orders/' . $cartOrder->id, [
            'status' => CartOrderStatus::LOCKED_FOR_PAYMENT,
            'remark' => 'Chot de thanh toan',
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.id', $cartOrder->id)
            ->assertJsonPath('metadata.status', CartOrderStatus::LOCKED_FOR_PAYMENT)
            ->assertJsonPath('metadata.remark', 'Chot de thanh toan');

        $cartOrder->refresh();

        $this->assertSame(CartOrderStatus::LOCKED_FOR_PAYMENT, $cartOrder->status);
        $this->assertSame('Chot de thanh toan', $cartOrder->remark);
    }

    public function test_authenticated_user_can_soft_delete_cart_order(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b04');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::OPEN);

        $this->actingAs($user, 'api')
            ->deleteJson('/api/v1/table/cart-orders/' . $cartOrder->id)
            ->assertOk();

        $this->assertDatabaseHas('cart_orders', [
            'id' => $cartOrder->id,
            'is_active' => false,
        ]);
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Administrator',
            'code' => 'ADMIN',
            'remark' => 'Cart order test admin role',
        ]);

        return User::query()->create([
            'is_active' => true,
            'user_name' => 'cart.order.admin',
            'full_name' => 'Cart Order Admin',
            'email' => 'cart-order-admin@example.com',
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

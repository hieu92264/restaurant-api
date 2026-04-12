<?php

namespace Tests\Feature;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\TableSessionStatus;
use App\Models\Category;
use App\Models\CartOrder;
use App\Models\Combo;
use App\Models\Dish;
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
        $category = $this->createCategory('do-uong');
        $dish = $this->createDish($category, 'com-suon-store', 'Com suon', 60000);

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/table/cart-orders', [
            'session_id' => $session->id,
            'table_id' => $table->id,
            'remark' => 'Order moi',
            'items' => [
                [
                    'item_type' => 'dish',
                    'item_id' => $dish->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.session_id', $session->id)
            ->assertJsonPath('metadata.table_id', $table->id)
            ->assertJsonPath('metadata.status', CartOrderStatus::OPEN)
            ->assertJsonPath('metadata.created_by_employee', $user->user_name)
            ->assertJsonPath('metadata.items.0.item_name_snapshot', 'Com suon')
            ->assertJsonPath('metadata.items.0.dish_id', $dish->id)
            ->assertJsonPath('metadata.subtotal_amount', 120000)
            ->assertJsonPath('metadata.total_amount', 120000);

        $this->assertDatabaseHas('cart_orders', [
            'session_id' => $session->id,
            'table_id' => $table->id,
            'status' => CartOrderStatus::OPEN,
            'created_by_employee' => $user->user_name,
            'total_amount' => 120000,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('cart_order_items', [
            'dish_id' => $dish->id,
            'item_name_snapshot' => 'Com suon',
            'line_total' => 120000,
        ]);
    }

    public function test_authenticated_user_cannot_store_cart_order_for_closed_session(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b02');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::CLOSED);
        $category = $this->createCategory('nuoc-uong');
        $dish = $this->createDish($category, 'tra-dao-store', 'Tra dao', 50000);

        $this->actingAs($user, 'api')->postJson('/api/v1/table/cart-orders', [
            'session_id' => $session->id,
            'table_id' => $table->id,
            'items' => [
                [
                    'item_type' => 'dish',
                    'item_id' => $dish->id,
                    'quantity' => 1,
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

    public function test_authenticated_user_can_sync_cart_order_items_from_simplified_payload(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b03-items');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::OPEN);
        $category = $this->createCategory('mon-chinh');
        $dishToRemove = $this->createDish($category, 'com-suon', 'Com suon', 60000);
        $dishToKeep = $this->createDish($category, 'tra-dao', 'Tra dao', 45000);
        $comboDish = $this->createDish($category, 'ga-ran', 'Ga ran', 30000);
        $combo = $this->createCombo('combo-ga', 'Combo ga', 5000, $comboDish, 2);

        $cartOrder->items()->create([
            'dish_id' => $dishToRemove->id,
            'combo_id' => null,
            'item_name_snapshot' => $dishToRemove->name,
            'variant_name_snapshot' => null,
            'quantity' => 2,
            'base_unit_price' => 60000,
            'option_total_price' => 0,
            'unit_final_price' => 60000,
            'line_total' => 120000,
            'item_note' => 'Khong hanh',
            'line_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $existingDishItem = $cartOrder->items()->create([
            'dish_id' => $dishToKeep->id,
            'combo_id' => null,
            'item_name_snapshot' => $dishToKeep->name,
            'variant_name_snapshot' => null,
            'quantity' => 1,
            'base_unit_price' => 45000,
            'option_total_price' => 0,
            'unit_final_price' => 45000,
            'line_total' => 45000,
            'item_note' => 'It da',
            'line_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/table/cart-orders/' . $cartOrder->id, [
            'items' => [
                [
                    'item_type' => 'dish',
                    'item_id' => $dishToKeep->id,
                    'quantity' => 3,
                ],
                [
                    'item_type' => 'COMBO',
                    'item_id' => $combo->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.items.0.item_name_snapshot', 'Tra dao')
            ->assertJsonPath('metadata.items.0.quantity', '3.00')
            ->assertJsonPath('metadata.items.1.item_name_snapshot', 'Combo ga')
            ->assertJsonPath('metadata.subtotal_amount', 190000)
            ->assertJsonPath('metadata.total_amount', 190000);

        $this->assertDatabaseMissing('cart_order_items', [
            'cart_order_id' => $cartOrder->id,
            'item_name_snapshot' => $dishToRemove->name,
        ]);

        $this->assertDatabaseHas('cart_order_items', [
            'id' => $existingDishItem->id,
            'cart_order_id' => $cartOrder->id,
            'dish_id' => $dishToKeep->id,
            'item_name_snapshot' => $dishToKeep->name,
            'quantity' => 3,
            'line_total' => 135000,
            'item_note' => 'It da',
        ]);

        $this->assertDatabaseHas('cart_order_items', [
            'cart_order_id' => $cartOrder->id,
            'dish_id' => null,
            'combo_id' => $combo->id,
            'item_name_snapshot' => $combo->name,
            'quantity' => 1,
            'line_total' => 55000,
        ]);

        $cartOrder->refresh();

        $this->assertSame(190000, $cartOrder->subtotal_amount);
        $this->assertSame(190000, $cartOrder->total_amount);
        $this->assertCount(2, $cartOrder->items()->get());
    }

    public function test_authenticated_user_can_show_current_open_cart_order_by_table(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b04');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);

        $olderCartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::OPEN);
        $olderCartOrder->update([
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        $newerCartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::LOCKED_FOR_PAYMENT);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/table/cart-orders/current-by-table/' . $table->id)
            ->assertOk()
            ->assertJsonPath('metadata.cart_order_id', $newerCartOrder->id)
            ->assertJsonPath('metadata.table_id', $table->id)
            ->assertJsonPath('metadata.status', CartOrderStatus::LOCKED_FOR_PAYMENT)
            ->assertJsonPath('metadata.item_list', []);
    }

    public function test_authenticated_user_gets_not_found_when_table_has_no_current_open_cart_order(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b05');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::CLOSED);
        $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::CONVERTED_TO_INVOICE);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/table/cart-orders/current-by-table/' . $table->id)
            ->assertNotFound();
    }

    public function test_authenticated_user_can_soft_delete_cart_order(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('b06');
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

    private function createCategory(string $slug): Category
    {
        return Category::query()->create([
            'slug' => $slug,
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'description' => 'Category for cart order test',
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    private function createDish(Category $category, string $slug, string $name, int $price): Dish
    {
        return Dish::query()->create([
            'category_id' => $category->id,
            'slug' => $slug,
            'name' => $name,
            'description' => 'Dish for cart order test',
            'price' => $price,
            'original_price' => $price,
            'cost_price' => max($price - 10000, 0),
            'image' => null,
            'unit' => 'phan',
            'is_featured' => false,
            'published_at' => now()->toDateString(),
            'status' => 'active',
            'available_from' => null,
            'available_to' => null,
            'sort_order' => 0,
            'options_json' => null,
            'tags_json' => null,
            'is_active' => true,
        ]);
    }

    private function createCombo(string $slug, string $name, int $discountPrice, Dish $dish, int $quantity): Combo
    {
        $combo = Combo::query()->create([
            'is_active' => true,
            'slug' => $slug,
            'name' => $name,
            'remark' => 'Combo for cart order test',
            'combo_image' => null,
            'discount_price' => $discountPrice,
            'max_use_times' => null,
            'tag' => null,
            'days_in_week' => null,
            'start_time' => null,
            'end_time' => null,
            'start_at' => null,
            'end_at' => null,
        ]);

        $combo->comboDishes()->create([
            'dish_id' => $dish->id,
            'quantity' => $quantity,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return $combo->fresh(['dishes']);
    }
}

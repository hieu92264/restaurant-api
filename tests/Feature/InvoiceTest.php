<?php

namespace Tests\Feature;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\InvoicePaymentStatus;
use App\Common\Constants\PaymentMethod;
use App\Common\Constants\TableSessionStatus;
use App\Models\Category;
use App\Models\CartOrder;
use App\Models\Dish;
use App\Models\Invoice;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\Reservation;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_paid_invoice_from_open_cart_order(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('c01');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::OPEN);

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/table/invoices', [
            'cart_order_id' => $cartOrder->id,
            'paid_amount' => 100000,
            'payment_method' => PaymentMethod::CASH,
            'customer_name' => 'Khach le',
            'customer_phone' => '0900000009',
            'note' => 'Thanh toan du',
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.cart_order_id', $cartOrder->id)
            ->assertJsonPath('metadata.session_id', $session->id)
            ->assertJsonPath('metadata.table_id', $table->id)
            ->assertJsonPath('metadata.payment_status', InvoicePaymentStatus::PAID)
            ->assertJsonPath('metadata.payment_method', PaymentMethod::CASH)
            ->assertJsonPath('metadata.table_name', $table->name)
            ->assertJsonPath('metadata.item_list.0.name', 'Com ga')
            ->assertJsonPath('metadata.item_list.0.unit_price', 100000)
            ->assertJsonPath('metadata.remark', 'Thanh toan du');

        $this->assertDatabaseHas('invoices', [
            'cart_order_id' => $cartOrder->id,
            'session_id' => $session->id,
            'table_id' => $table->id,
            'payment_status' => InvoicePaymentStatus::PAID,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'change_amount' => 0,
            'payment_method' => PaymentMethod::CASH,
            'created_by_employee' => $user->user_name,
            'customer_name' => 'Khach le',
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'dish_id' => Dish::query()->where('slug', 'com-ga')->value('id'),
            'item_name_snapshot' => 'Com ga',
            'line_total' => 100000,
        ]);

        $cartOrder->refresh();
        $session->refresh();

        $this->assertSame(CartOrderStatus::CONVERTED_TO_INVOICE, $cartOrder->status);
        $this->assertSame(TableSessionStatus::PAID, $session->status);
        $this->assertNotNull($session->closed_at);
        $this->assertSame($user->user_name, $session->closed_by_employee);
    }

    public function test_authenticated_user_can_store_partial_invoice_and_session_becomes_payment_pending(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('c02');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::LOCKED_FOR_PAYMENT);

        $this->actingAs($user, 'api')->postJson('/api/v1/table/invoices', [
            'cart_order_id' => $cartOrder->id,
            'paid_amount' => 30000,
            'payment_method' => PaymentMethod::TRANSFER,
        ])->assertCreated()
            ->assertJsonPath('metadata.payment_status', InvoicePaymentStatus::PARTIAL)
            ->assertJsonPath('metadata.remaining_amount', 70000)
            ->assertJsonPath('metadata.change_amount', 0)
            ->assertJsonPath('metadata.payment_method', PaymentMethod::TRANSFER);

        $session->refresh();
        $cartOrder->refresh();

        $this->assertSame(TableSessionStatus::PAYMENT_PENDING, $session->status);
        $this->assertNull($session->closed_at);
        $this->assertSame(CartOrderStatus::CONVERTED_TO_INVOICE, $cartOrder->status);
    }

    public function test_authenticated_user_cannot_store_invoice_when_cart_order_status_is_invalid(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('c05');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::CANCELLED);

        $this->actingAs($user, 'api')->postJson('/api/v1/table/invoices', [
            'cart_order_id' => $cartOrder->id,
            'paid_amount' => 100000,
            'payment_method' => PaymentMethod::CASH,
        ])->assertBadRequest();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_paid_invoice_completes_attached_reservation(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('c06');
        $reservation = $this->createReservation($table, $user->user_name, 'RSV-C06');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::OPEN, $reservation->reservation_code);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::LOCKED_FOR_PAYMENT);

        $this->actingAs($user, 'api')->postJson('/api/v1/table/invoices', [
            'cart_order_id' => $cartOrder->id,
            'paid_amount' => 100000,
            'payment_method' => PaymentMethod::CASH,
        ])->assertCreated();

        $reservation->refresh();

        $this->assertSame('completed', $reservation->status);
        $this->assertFalse($reservation->is_active);
    }

    public function test_authenticated_user_cannot_store_duplicate_active_invoice_for_same_cart_order(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('c03');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::PAYMENT_PENDING);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::CONVERTED_TO_INVOICE);
        $invoice = $this->createInvoice($cartOrder, $session, $table, $user->user_name, 100000, 0, InvoicePaymentStatus::UNPAID);

        $this->actingAs($user, 'api')->postJson('/api/v1/table/invoices', [
            'cart_order_id' => $cartOrder->id,
        ])->assertBadRequest();

        $this->assertDatabaseCount('invoices', 1);
        $this->assertSame($invoice->id, Invoice::query()->firstOrFail()->id);
    }

    public function test_authenticated_user_can_update_invoice_payment_to_paid(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('c04');
        $session = $this->createSession($table, $user->user_name, TableSessionStatus::PAYMENT_PENDING);
        $cartOrder = $this->createCartOrder($session, $table, $user->user_name, CartOrderStatus::CONVERTED_TO_INVOICE);
        $invoice = $this->createInvoice($cartOrder, $session, $table, $user->user_name, 100000, 20000, InvoicePaymentStatus::PARTIAL);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/table/invoices/' . $invoice->id, [
            'paid_amount' => 100000,
            'payment_method' => PaymentMethod::CARD,
            'note' => 'Khach vua quet the',
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.id', $invoice->id)
            ->assertJsonPath('metadata.payment_status', InvoicePaymentStatus::PAID)
            ->assertJsonPath('metadata.remaining_amount', 0)
            ->assertJsonPath('metadata.payment_method', PaymentMethod::CARD)
            ->assertJsonPath('metadata.remark', 'Khach vua quet the');

        $invoice->refresh();
        $session->refresh();

        $this->assertSame(100000, $invoice->paid_amount);
        $this->assertSame(0, $invoice->remaining_amount);
        $this->assertSame(0, $invoice->change_amount);
        $this->assertNotNull($invoice->paid_at);
        $this->assertSame(TableSessionStatus::PAID, $session->status);
        $this->assertNotNull($session->closed_at);
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Administrator',
            'code' => 'ADMIN',
            'remark' => 'Invoice test admin role',
        ]);

        return User::query()->create([
            'is_active' => true,
            'user_name' => 'invoice.admin',
            'full_name' => 'Invoice Admin',
            'email' => 'invoice-admin@example.com',
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
        string $status,
        ?string $reservationCode = null
    ): TableSession {
        return TableSession::query()->create([
            'table_id' => $table->id,
            'opened_by_employee' => $openedByEmployee,
            'guest_count' => 4,
            'status' => $status,
            'opened_at' => now()->subHour(),
            'closed_at' => null,
            'reservation_code' => $reservationCode,
            'remark' => 'Session invoice test',
            'is_active' => true,
        ]);
    }

    private function createCartOrder(
        TableSession $session,
        RestaurantTable $table,
        string $createdByEmployee,
        string $status
    ): CartOrder {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'mon-chinh'],
            [
                'name' => 'Mon chinh',
                'description' => null,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $dish = Dish::query()->firstOrCreate(
            ['slug' => 'com-ga'],
            [
                'category_id' => $category->id,
                'name' => 'Com ga',
                'description' => null,
                'price' => 100000,
                'original_price' => 100000,
                'cost_price' => 50000,
                'image' => json_encode(['url' => 'storage/dishes/com-ga.webp']),
                'unit' => 'phan',
                'is_featured' => false,
                'published_at' => now()->toDateString(),
                'status' => 'available',
                'available_from' => null,
                'available_to' => null,
                'sort_order' => 1,
                'options_json' => [],
                'tags_json' => [],
                'is_active' => true,
            ]
        );

        $cartOrder = CartOrder::query()->create([
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
            'remark' => 'Order invoice test',
            'is_active' => true,
        ]);

        $cartOrder->items()->create([
            'dish_id' => $dish->id,
            'combo_id' => null,
            'item_name_snapshot' => 'Com ga',
            'variant_name_snapshot' => null,
            'quantity' => 1,
            'base_unit_price' => 100000,
            'option_total_price' => 0,
            'unit_final_price' => 100000,
            'line_total' => 100000,
            'item_note' => null,
            'line_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        return $cartOrder;
    }

    private function createInvoice(
        CartOrder $cartOrder,
        TableSession $session,
        RestaurantTable $table,
        string $createdByEmployee,
        int $totalAmount,
        int $paidAmount,
        string $paymentStatus
    ): Invoice {
        $invoice = Invoice::query()->create([
            'no' => 'INV-' . $cartOrder->id . '-' . strtolower($paymentStatus),
            'cart_order_id' => $cartOrder->id,
            'session_id' => $session->id,
            'table_id' => $table->id,
            'reservation_code' => null,
            'created_by_employee' => $createdByEmployee,
            'customer_name' => null,
            'customer_phone' => null,
            'subtotal_amount' => $totalAmount,
            'discount_amount' => 0,
            'service_charge_amount' => 0,
            'tax_amount' => 0,
            'deposit_amount' => 0,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => max($totalAmount - $paidAmount, 0),
            'change_amount' => max($paidAmount - $totalAmount, 0),
            'payment_method' => null,
            'payment_status' => $paymentStatus,
            'issued_at' => now()->subMinutes(10),
            'paid_at' => $paymentStatus === InvoicePaymentStatus::PAID ? now()->subMinutes(5) : null,
            'note' => 'Invoice test',
            'is_active' => true,
        ]);

        $invoice->items()->create([
            'dish_id' => Dish::query()->where('slug', 'com-ga')->value('id'),
            'combo_id' => null,
            'item_name_snapshot' => 'Com ga',
            'variant_name_snapshot' => null,
            'quantity' => 1,
            'base_unit_price' => 100000,
            'option_total_price' => 0,
            'unit_final_price' => 100000,
            'line_total' => 100000,
            'item_note' => null,
            'is_active' => true,
        ]);

        return $invoice;
    }

    private function createReservation(RestaurantTable $table, string $createdByEmployee, string $reservationCode): Reservation
    {
        return Reservation::query()->create([
            'is_active' => true,
            'reservation_code' => $reservationCode,
            'customer_name' => 'Khach reservation',
            'customer_phone' => '0900000011',
            'guest_count' => 4,
            'reservation_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'remark' => 'Reservation invoice test',
            'status' => 'confirmed',
            'deposit_amount' => 0,
            'hold_start_time' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
            'hold_end_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'created_by_employee' => $createdByEmployee,
            'confirmed_by_employee' => $createdByEmployee,
            'confirmed_at' => now()->subMinutes(30)->format('Y-m-d H:i:s'),
            'table_code' => $table->slug,
        ]);
    }
}

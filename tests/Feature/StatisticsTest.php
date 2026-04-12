<?php

namespace Tests\Feature;

use App\Common\Constants\InvoicePaymentStatus;
use App\Common\Constants\TableSessionStatus;
use App\Models\Category;
use App\Models\CartOrder;
use App\Models\Combo;
use App\Models\Invoice;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_statistics_routes(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('s01');
        $combo = $this->createCombo();

        $aprilSessionOne = $this->createSession($table, $user->user_name, '2026-04-10 10:00:00', '2026-04-10 11:00:00', TableSessionStatus::PAID);
        $aprilSessionTwo = $this->createSession($table, $user->user_name, '2026-04-11 12:00:00', '2026-04-11 13:30:00', TableSessionStatus::CLOSED);
        $marchSession = $this->createSession($table, $user->user_name, '2026-03-15 18:00:00', '2026-03-15 19:00:00', TableSessionStatus::PAID);

        $aprilInvoiceOne = $this->createPaidInvoice($table, $aprilSessionOne, $user->user_name, '2026-04-10 11:05:00', 180000);
        $aprilInvoiceOne->items()->createMany([
            [
                'combo_id' => null,
                'item_name_snapshot' => 'Com ga',
                'variant_name_snapshot' => null,
                'quantity' => 2,
                'base_unit_price' => 50000,
                'option_total_price' => 0,
                'unit_final_price' => 50000,
                'line_total' => 100000,
                'item_note' => null,
                'is_active' => true,
            ],
            [
                'combo_id' => $combo->id,
                'item_name_snapshot' => 'Combo trua',
                'variant_name_snapshot' => null,
                'quantity' => 2,
                'base_unit_price' => 40000,
                'option_total_price' => 0,
                'unit_final_price' => 40000,
                'line_total' => 80000,
                'item_note' => null,
                'is_active' => true,
            ],
        ]);

        $aprilInvoiceTwo = $this->createPaidInvoice($table, $aprilSessionTwo, $user->user_name, '2026-04-11 13:35:00', 120000);
        $aprilInvoiceTwo->items()->createMany([
            [
                'combo_id' => null,
                'item_name_snapshot' => 'Bun bo',
                'variant_name_snapshot' => null,
                'quantity' => 1,
                'base_unit_price' => 80000,
                'option_total_price' => 0,
                'unit_final_price' => 80000,
                'line_total' => 80000,
                'item_note' => null,
                'is_active' => true,
            ],
            [
                'combo_id' => $combo->id,
                'item_name_snapshot' => 'Combo trua',
                'variant_name_snapshot' => null,
                'quantity' => 1,
                'base_unit_price' => 40000,
                'option_total_price' => 0,
                'unit_final_price' => 40000,
                'line_total' => 40000,
                'item_note' => null,
                'is_active' => true,
            ],
        ]);

        $marchInvoice = $this->createPaidInvoice($table, $marchSession, $user->user_name, '2026-03-15 19:10:00', 50000);
        $marchInvoice->items()->create([
            'combo_id' => null,
            'item_name_snapshot' => 'Pho bo',
            'variant_name_snapshot' => null,
            'quantity' => 1,
            'base_unit_price' => 50000,
            'option_total_price' => 0,
            'unit_final_price' => 50000,
            'line_total' => 50000,
            'item_note' => null,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/statistics/revenue?year=2026&month=4')
            ->assertOk()
            ->assertJsonPath('metadata.filter.year', 2026)
            ->assertJsonPath('metadata.filter.month', 4)
            ->assertJsonPath('metadata.month.total_amount', 300000)
            ->assertJsonPath('metadata.year.total_amount', 350000);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/statistics/average-service-time?year=2026&month=4')
            ->assertOk()
            ->assertJsonPath('metadata.average_service_time.served_sessions', 2)
            ->assertJsonPath('metadata.average_service_time.average_minutes', 75);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/statistics/top-dishes?year=2026&month=4&top_limit=3')
            ->assertOk()
            ->assertJsonPath('metadata.items.0.name', 'Com ga')
            ->assertJsonPath('metadata.items.0.quantity_sold', 2);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/statistics/top-combos?year=2026&month=4&top_limit=3')
            ->assertOk()
            ->assertJsonPath('metadata.items.0.name', 'Combo trua')
            ->assertJsonPath('metadata.items.0.quantity_sold', 3);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/statistics/summary?year=2026&month=4&top_limit=3')
            ->assertOk()
            ->assertJsonPath('metadata.filter.year', 2026)
            ->assertJsonPath('metadata.filter.month', 4)
            ->assertJsonPath('metadata.revenue.month.total_amount', 300000)
            ->assertJsonPath('metadata.revenue.year.total_amount', 350000)
            ->assertJsonPath('metadata.average_service_time.served_sessions', 2)
            ->assertJsonPath('metadata.average_service_time.average_minutes', 75)
            ->assertJsonPath('metadata.top_dishes.0.name', 'Com ga')
            ->assertJsonPath('metadata.top_dishes.0.quantity_sold', 2)
            ->assertJsonPath('metadata.top_combos.0.name', 'Combo trua')
            ->assertJsonPath('metadata.top_combos.0.quantity_sold', 3);
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Administrator',
            'code' => 'ADMIN',
            'remark' => 'Statistics test admin role',
        ]);

        return User::query()->create([
            'is_active' => true,
            'user_name' => 'statistics.admin',
            'full_name' => 'Statistics Admin',
            'email' => 'statistics-admin@example.com',
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

    private function createCombo(): Combo
    {
        Category::query()->firstOrCreate(
            ['slug' => 'combo-category'],
            [
                'name' => 'Combo category',
                'description' => null,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        return Combo::query()->create([
            'slug' => 'combo-trua',
            'name' => 'Combo trua',
            'remark' => null,
            'combo_image' => null,
            'discount_price' => 0,
            'max_use_times' => null,
            'tag' => null,
            'days_in_week' => null,
            'start_time' => null,
            'end_time' => null,
            'start_at' => null,
            'end_at' => null,
            'is_active' => true,
        ]);
    }

    private function createSession(
        RestaurantTable $table,
        string $userName,
        string $openedAt,
        string $closedAt,
        string $status
    ): TableSession {
        return TableSession::query()->create([
            'table_id' => $table->id,
            'opened_by_employee' => $userName,
            'closed_by_employee' => $userName,
            'guest_count' => 4,
            'status' => $status,
            'opened_at' => $openedAt,
            'closed_at' => $closedAt,
            'remark' => 'Statistics session',
            'is_active' => true,
        ]);
    }

    private function createPaidInvoice(
        RestaurantTable $table,
        TableSession $session,
        string $userName,
        string $paidAt,
        int $totalAmount
    ): Invoice {
        $cartOrder = CartOrder::query()->create([
            'session_id' => $session->id,
            'table_id' => $table->id,
            'order_no' => 'ORD-STAT-' . $session->id . '-' . str_replace([' ', ':'], '-', $paidAt),
            'created_by_employee' => $userName,
            'status' => 'CONVERTED_TO_INVOICE',
            'subtotal_amount' => $totalAmount,
            'discount_amount' => 0,
            'service_charge_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $totalAmount,
            'remark' => 'Statistics order',
            'is_active' => true,
        ]);

        return Invoice::query()->create([
            'no' => 'INV-STAT-' . $session->id . '-' . str_replace([' ', ':'], '-', $paidAt),
            'cart_order_id' => $cartOrder->id,
            'session_id' => $session->id,
            'table_id' => $table->id,
            'reservation_code' => null,
            'created_by_employee' => $userName,
            'customer_name' => 'Khach thong ke',
            'customer_phone' => '0900000013',
            'subtotal_amount' => $totalAmount,
            'discount_amount' => 0,
            'service_charge_amount' => 0,
            'tax_amount' => 0,
            'deposit_amount' => 0,
            'total_amount' => $totalAmount,
            'paid_amount' => $totalAmount,
            'remaining_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'CASH',
            'payment_status' => InvoicePaymentStatus::PAID,
            'issued_at' => $paidAt,
            'paid_at' => $paidAt,
            'note' => 'Statistics invoice',
            'is_active' => true,
        ]);
    }
}

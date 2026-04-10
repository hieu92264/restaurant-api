<?php

namespace Tests\Feature;

use App\Common\Constants\ReservationStatus;
use App\Common\Constants\TableSessionStatus;
use App\Http\interfaces\ITableStatusService;
use App\Mail\CustomerReservationCreatedMail;
use App\Mail\ExpiredReservationAutoCanceledMail;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\TableSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_reservation_with_table(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a01');

        $reservationTime = Carbon::now()->addDay()->setTime(18, 0);

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/reservations', [
            'customer_name' => 'Nguyen Van A',
            'customer_phone' => '0900000001',
            'guest_count' => 4,
            'reservation_time' => $reservationTime->format('Y-m-d H:i:s'),
            'table_code' => $table->slug,
            'remark' => 'Khach dat toi',
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.customer_name', 'Nguyen Van A')
            ->assertJsonPath('metadata.table_code', $table->slug)
            ->assertJsonPath('metadata.created_by_employee', $user->user_name);

        $reservationCode = (string) $response->json('metadata.reservation_code');
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $reservationCode);

        $this->assertDatabaseHas('reservations', [
            'customer_name' => 'Nguyen Van A',
            'table_code' => $table->slug,
            'status' => ReservationStatus::PENDING,
            'created_by_employee' => $user->user_name,
            'is_active' => true,
        ]);
    }

    public function test_customer_store_by_customer_sends_email_to_managers(): void
    {
        Mail::fake();

        $managerRole = Role::query()->create([
            'name' => 'Manager',
            'code' => 'MANAGER',
            'remark' => 'Reservation manager role',
        ]);

        User::query()->create([
            'is_active' => true,
            'user_name' => 'reservation.manager',
            'full_name' => 'Reservation Manager',
            'email' => 'manager@example.com',
            'password' => 'password',
            'role_id' => $managerRole->id,
        ]);

        $reservationTime = Carbon::now()->addDay()->setTime(19, 0);

        $response = $this->postJson('/api/v1/reservations/store-by-customer', [
            'customer_name' => 'Tran Thi B',
            'customer_phone' => '0900000999',
            'guest_count' => 6,
            'reservation_time' => $reservationTime->format('Y-m-d H:i:s'),
            'remark' => 'Khach dat sinh nhat',
        ]);

        $response->assertCreated()
            ->assertJsonPath('metadata.customer_name', 'Tran Thi B')
            ->assertJsonPath('metadata.customer_phone', '0900000999')
            ->assertJsonPath('metadata.status', ReservationStatus::PENDING);

        $reservationCode = (string) $response->json('metadata.reservation_code');
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $reservationCode);

        Mail::assertQueued(CustomerReservationCreatedMail::class, function (CustomerReservationCreatedMail $mail) {
            return $mail->hasTo('manager@example.com')
                && $mail->reservation->customer_name === 'Tran Thi B'
                && $mail->reservation->customer_phone === '0900000999';
        });
    }

    public function test_expired_reservation_is_auto_canceled_and_notifies_managers(): void
    {
        Mail::fake();

        $spy = new ReservationTableStatusServiceSpy();
        $this->app->instance(ITableStatusService::class, $spy);

        $managerRole = Role::query()->create([
            'name' => 'Manager',
            'code' => 'MANAGER',
            'remark' => 'Reservation manager role',
        ]);

        User::query()->create([
            'is_active' => true,
            'user_name' => 'reservation.manager.auto.cancel',
            'full_name' => 'Reservation Manager',
            'email' => 'manager@example.com',
            'password' => 'password',
            'role_id' => $managerRole->id,
        ]);

        $table = $this->createTable('a05');
        $reservationTime = Carbon::parse('2026-04-09 18:00:00');

        $reservation = Reservation::query()->create([
            'is_active' => true,
            'reservation_code' => 'EXPIRED-001',
            'customer_name' => 'Khach qua han',
            'customer_phone' => '0900999000',
            'guest_count' => 4,
            'reservation_time' => $reservationTime->format('Y-m-d H:i:s'),
            'status' => ReservationStatus::CONFIRMED,
            'deposit_amount' => 0,
            'hold_start_time' => $reservationTime->copy()->subHour()->format('Y-m-d H:i:s'),
            'hold_end_time' => $reservationTime->copy()->addMinutes(15)->format('Y-m-d H:i:s'),
            'table_code' => $table->slug,
        ]);

        Carbon::setTestNow('2026-04-09 18:20:00');

        try {
            $this->artisan('reservations:auto-cancel-expired')
                ->assertSuccessful();
        } finally {
            Carbon::setTestNow();
        }

        $reservation->refresh();

        $this->assertFalse($reservation->is_active);
        $this->assertSame(ReservationStatus::CANCELED, $reservation->status);
        $this->assertNull($reservation->cancelled_by_employee);
        $this->assertNotNull($reservation->cancelled_at);
        $this->assertSame([$table->slug], $spy->syncedTableCodes);

        Mail::assertQueued(ExpiredReservationAutoCanceledMail::class, function (ExpiredReservationAutoCanceledMail $mail) use ($reservation) {
            return $mail->hasTo('manager@example.com')
                && $mail->reservation->is($reservation);
        });
    }

    public function test_expired_reservation_with_table_session_is_not_auto_canceled(): void
    {
        Mail::fake();

        $spy = new ReservationTableStatusServiceSpy();
        $this->app->instance(ITableStatusService::class, $spy);

        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a06');
        $reservationTime = Carbon::parse('2026-04-09 19:00:00');

        $reservation = Reservation::query()->create([
            'is_active' => true,
            'reservation_code' => 'EXPIRED-SESSION-001',
            'customer_name' => 'Khach da vao ban',
            'customer_phone' => '0900111222',
            'guest_count' => 2,
            'reservation_time' => $reservationTime->format('Y-m-d H:i:s'),
            'status' => ReservationStatus::CONFIRMED,
            'deposit_amount' => 0,
            'hold_start_time' => $reservationTime->copy()->subHour()->format('Y-m-d H:i:s'),
            'hold_end_time' => $reservationTime->copy()->addMinutes(15)->format('Y-m-d H:i:s'),
            'table_code' => $table->slug,
        ]);

        TableSession::query()->create([
            'table_id' => $table->id,
            'opened_by_employee' => $user->user_name,
            'guest_count' => 2,
            'status' => TableSessionStatus::OPEN,
            'opened_at' => $reservationTime->copy()->format('Y-m-d H:i:s'),
            'reservation_code' => $reservation->reservation_code,
            'is_active' => true,
        ]);

        Carbon::setTestNow('2026-04-09 19:30:00');

        try {
            $this->artisan('reservations:auto-cancel-expired')
                ->assertSuccessful();
        } finally {
            Carbon::setTestNow();
        }

        $reservation->refresh();

        $this->assertTrue($reservation->is_active);
        $this->assertSame(ReservationStatus::CONFIRMED, $reservation->status);
        $this->assertSame([], $spy->syncedTableCodes);

        Mail::assertNotQueued(ExpiredReservationAutoCanceledMail::class);
    }

    public function test_authenticated_user_cannot_store_reservation_when_table_time_overlaps(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a02');
        $reservationTime = Carbon::now()->addDay()->setTime(18, 0);

        Reservation::query()->create([
            'is_active' => true,
            'reservation_code' => 'EXISTING-001',
            'customer_name' => 'Khach cu',
            'customer_phone' => '0900000002',
            'guest_count' => 4,
            'reservation_time' => $reservationTime->copy()->format('Y-m-d H:i:s'),
            'status' => ReservationStatus::CONFIRMED,
            'deposit_amount' => 0,
            'hold_start_time' => $reservationTime->copy()->subHour()->format('Y-m-d H:i:s'),
            'hold_end_time' => $reservationTime->copy()->addMinutes(15)->format('Y-m-d H:i:s'),
            'table_code' => $table->slug,
        ]);

        $this->actingAs($user, 'api')->postJson('/api/v1/reservations', [
            'customer_name' => 'Khach moi',
            'customer_phone' => '0900000003',
            'guest_count' => 2,
            'reservation_time' => $reservationTime->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            'hold_start_time' => $reservationTime->copy()->subMinutes(15)->format('Y-m-d H:i:s'),
            'hold_end_time' => $reservationTime->copy()->addHour()->format('Y-m-d H:i:s'),
            'table_code' => $table->slug,
        ])->assertBadRequest();

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_authenticated_user_can_patch_reservation_remark_without_full_payload(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a03');
        $reservation = $this->createReservation($table->slug, $user->user_name);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v1/reservations/' . $reservation->reservation_code, [
            'remark' => 'Khach doi them 10 phut',
        ]);

        $response->assertOk()
            ->assertJsonPath('metadata.remark', 'Khach doi them 10 phut');

        $reservation->refresh();

        $this->assertSame('Khach doi them 10 phut', $reservation->remark);
    }

    public function test_destroy_reservation_soft_cancels_reservation(): void
    {
        $user = $this->createAuthenticatedUser();
        $table = $this->createTable('a04');
        $reservation = $this->createReservation($table->slug, $user->user_name);

        $response = $this->actingAs($user, 'api')->deleteJson('/api/v1/reservations/' . $reservation->reservation_code);

        $response->assertOk()
            ->assertJsonPath('metadata.status', ReservationStatus::CANCELED)
            ->assertJsonPath('metadata.is_active', false);

        $reservation->refresh();

        $this->assertFalse($reservation->is_active);
        $this->assertSame(ReservationStatus::CANCELED, $reservation->status);
        $this->assertSame($user->user_name, $reservation->cancelled_by_employee);
        $this->assertNotNull($reservation->cancelled_at);
    }

    public function test_show_returns_not_found_when_reservation_does_not_exist(): void
    {
        $user = $this->createAuthenticatedUser();

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/reservations/NOT-FOUND')
            ->assertNotFound();
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Administrator',
            'code' => 'ADMIN',
            'remark' => 'Reservation test admin role',
        ]);

        return User::query()->create([
            'is_active' => true,
            'user_name' => 'reservation.admin',
            'full_name' => 'Reservation Admin',
            'email' => 'reservation-admin@example.com',
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

    private function createReservation(string $tableCode, string $createdByEmployee): Reservation
    {
        $reservationTime = Carbon::now()->addDay()->setTime(18, 0);

        return Reservation::query()->create([
            'is_active' => true,
            'reservation_code' => 'RES-' . strtoupper($tableCode),
            'customer_name' => 'Khach test',
            'customer_phone' => '0900000010',
            'guest_count' => 4,
            'reservation_time' => $reservationTime->format('Y-m-d H:i:s'),
            'remark' => 'Ghi chu cu',
            'status' => ReservationStatus::PENDING,
            'deposit_amount' => 0,
            'hold_start_time' => $reservationTime->copy()->subHour()->format('Y-m-d H:i:s'),
            'hold_end_time' => $reservationTime->copy()->addMinutes(15)->format('Y-m-d H:i:s'),
            'created_by_employee' => $createdByEmployee,
            'table_code' => $tableCode,
        ]);
    }
}

class ReservationTableStatusServiceSpy implements ITableStatusService
{
    public array $syncedTableCodes = [];

    public function syncTableStatus(string $tableCode): void
    {
        $this->syncedTableCodes[] = $tableCode;
    }
}

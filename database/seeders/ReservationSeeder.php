<?php

namespace Database\Seeders;

use App\Common\Constants\ReservationStatus;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::withoutGlobalScopes()->where('user_name', 'manager')->first();
        $cashier = User::withoutGlobalScopes()->where('user_name', 'cashier')->first();

        $tableA02 = RestaurantTable::withoutGlobalScopes()->where('slug', 'A02')->first();
        $tableVip01 = RestaurantTable::withoutGlobalScopes()->where('slug', 'VIP01')->first();

        $now = now();

        $reservations = [
            [
                'reservation_code' => 'A02RSV',
                'customer_name' => 'Khach giu ban A02',
                'customer_phone' => '0900001001',
                'guest_count' => 4,
                'reservation_time' => $now->copy()->addMinutes(30),
                'remark' => 'Reservation dang trong thoi gian giu ban',
                'status' => ReservationStatus::CONFIRMED,
                'deposit_amount' => 200000,
                'confirmed_at' => $now->copy()->subMinutes(20),
                'cancelled_at' => null,
                'hold_start_time' => $now->copy()->subMinutes(30),
                'hold_end_time' => $now->copy()->addMinutes(45),
                'created_by_employee' => $manager?->user_name,
                'confirmed_by_employee' => $manager?->user_name,
                'cancelled_by_employee' => null,
                'table_code' => $tableA02?->slug,
                'is_active' => true,
            ],
            [
                'reservation_code' => 'VIP123',
                'customer_name' => 'Khach VIP tuong lai',
                'customer_phone' => '0900001002',
                'guest_count' => 8,
                'reservation_time' => $now->copy()->addHours(4),
                'remark' => 'Reservation tuong lai, chua vao khoang giu ban',
                'status' => ReservationStatus::PENDING,
                'deposit_amount' => 0,
                'confirmed_at' => null,
                'cancelled_at' => null,
                'hold_start_time' => $now->copy()->addHours(3),
                'hold_end_time' => $now->copy()->addHours(4)->addMinutes(15),
                'created_by_employee' => $cashier?->user_name,
                'confirmed_by_employee' => null,
                'cancelled_by_employee' => null,
                'table_code' => $tableVip01?->slug,
                'is_active' => true,
            ],
        ];

        foreach ($reservations as $reservationData) {
            Reservation::withoutGlobalScopes()->updateOrCreate(
                ['reservation_code' => $reservationData['reservation_code']],
                $reservationData
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Common\Constants\TableSessionStatus;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class TableSessionSeeder extends Seeder
{
    public function run(): void
    {
        $waiter = User::withoutGlobalScopes()->where('user_name', 'waiter')->first();
        $cashier = User::withoutGlobalScopes()->where('user_name', 'cashier')->first();
        $now = now();

        $sessions = [
            [
                'table_slug' => 'A01',
                'opened_by_employee' => $waiter?->user_name,
                'closed_by_employee' => null,
                'guest_count' => 3,
                'status' => TableSessionStatus::OPEN,
                'opened_at' => $now->copy()->subMinutes(45),
                'closed_at' => null,
                'remark' => 'Khach muon len mon tung dot',
                'reservation_code' => null,
            ],
            [
                'table_slug' => 'G01',
                'opened_by_employee' => $waiter?->user_name,
                'closed_by_employee' => $cashier?->user_name,
                'guest_count' => 2,
                'status' => TableSessionStatus::CLOSED,
                'opened_at' => $now->copy()->subDay()->setTime(11, 45),
                'closed_at' => $now->copy()->subDay()->setTime(12, 35),
                'remark' => 'Khach yeu cau xuat hoa don cong ty',
                'reservation_code' => null,
            ],
        ];

        foreach ($sessions as $sessionData) {
            $table = RestaurantTable::withoutGlobalScopes()
                ->where('slug', $sessionData['table_slug'])
                ->first();

            TableSession::withoutGlobalScopes()->updateOrCreate(
                [
                    'table_id' => $table?->id,
                    'opened_at' => $sessionData['opened_at'],
                ],
                [
                    'opened_by_employee' => $sessionData['opened_by_employee'],
                    'closed_by_employee' => $sessionData['closed_by_employee'],
                    'guest_count' => $sessionData['guest_count'],
                    'status' => $sessionData['status'],
                    'closed_at' => $sessionData['closed_at'],
                    'remark' => $sessionData['remark'],
                    'reservation_code' => $sessionData['reservation_code'],
                    'is_active' => true,
                ]
            );
        }
    }
}

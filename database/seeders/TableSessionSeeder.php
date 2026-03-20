<?php

namespace Database\Seeders;

use App\Common\Enums\ActiveStatus;
use App\Common\Enums\TableSessionStatus;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class TableSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waiter = User::where('user_name', 'waiter')->first();
        $cashier = User::where('user_name', 'cashier')->first();

        $sessions = [
            [
                'table_code' => 'A01',
                'opened_by_employee_id' => $waiter?->id,
                'closed_by_employee_id' => null,
                'guest_count' => 3,
                'status' => TableSessionStatus::OPEN,
                'opened_at' => '2026-03-20 18:30:00',
                'closed_at' => null,
                'remark' => 'Khach muon len mon tung dot',
            ],
            [
                'table_code' => 'G01',
                'opened_by_employee_id' => $waiter?->id,
                'closed_by_employee_id' => $cashier?->id,
                'guest_count' => 2,
                'status' => TableSessionStatus::CLOSED,
                'opened_at' => '2026-03-20 11:45:00',
                'closed_at' => '2026-03-20 12:35:00',
                'remark' => 'Khach yeu cau xuat hoa don cong ty',
            ],
        ];

        foreach ($sessions as $sessionData) {
            $table = RestaurantTable::withoutGlobalScopes()->where('code', $sessionData['table_code'])->first();

            TableSession::withoutGlobalScopes()->updateOrCreate(
                [
                    'table_id' => $table?->id,
                    'opened_at' => $sessionData['opened_at'],
                ],
                [
                    'opened_by_employee_id' => $sessionData['opened_by_employee_id'],
                    'closed_by_employee_id' => $sessionData['closed_by_employee_id'],
                    'guest_count' => $sessionData['guest_count'],
                    'status' => $sessionData['status'],
                    'closed_at' => $sessionData['closed_at'],
                    'remark' => $sessionData['remark'],
                    'is_active' => ActiveStatus::YES,
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Common\Constants\TableSessionStatus;
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
                'table_slug' => 'A01',
                'opened_by_employee' => $waiter?->user_name,
                'closed_by_employee' => null,
                'guest_count' => 3,
                'status' => TableSessionStatus::OPEN,
                'opened_at' => '2026-03-20 18:30:00',
                'closed_at' => null,
                'remark' => 'Khách muốn lên món từng đợt',
            ],
            [
                'table_slug' => 'G01',
                'opened_by_employee' => $waiter?->user_name,
                'closed_by_employee' => $cashier?->user_name,
                'guest_count' => 2,
                'status' => TableSessionStatus::CLOSED,
                'opened_at' => '2026-03-20 11:45:00',
                'closed_at' => '2026-03-20 12:35:00',
                'remark' => 'Khách yêu cầu xuất hóa đơn công ty',
            ],
        ];

        foreach ($sessions as $sessionData) {
            $table = RestaurantTable::withoutGlobalScopes()->where('slug', $sessionData['table_slug'])->first();

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
                    'is_active' => true,
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::where('code', 'OWNER')->first();
        $managerRole = Role::where('code', 'MANAGER')->first();
        $cashierRole = Role::where('code', 'CASHIER')->first();
        $waiterRole = Role::where('code', 'WAITER')->first();
        $kitchenRole = Role::where('code', 'KITCHEN')->first();

        $users = [
            [
                'is_active' => 'Y',
                'user_name' => 'owner',
                'full_name' => 'Chủ Nhà Hàng',
                'email' => 'owner@restaurant.com',
                'password' => '12345678',
                'role_id' => $ownerRole?->id,
            ],
            [
                'is_active' => 'Y',
                'user_name' => 'manager',
                'full_name' => 'Quản Lý Nhà Hàng',
                'email' => 'manager@restaurant.com',
                'password' => '12345678',
                'role_id' => $managerRole?->id,
            ],
            [
                'is_active' => 'Y',
                'user_name' => 'cashier',
                'full_name' => 'Thu Ngân',
                'email' => 'cashier@restaurant.com',
                'password' => '12345678',
                'role_id' => $cashierRole?->id,
            ],
            [
                'is_active' => 'Y',
                'user_name' => 'waiter',
                'full_name' => 'Nhân Viên Phục Vụ Bàn',
                'email' => 'waiter@restaurant.com',
                'password' => '12345678',
                'role_id' => $waiterRole?->id,
            ],
            [
                'is_active' => 'Y',
                'user_name' => 'kitchen',
                'full_name' => 'Nhân Viên Nhà Bếp',
                'email' => 'kitchen@restaurant.com',
                'password' => '12345678',
                'role_id' => $kitchenRole?->id,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}

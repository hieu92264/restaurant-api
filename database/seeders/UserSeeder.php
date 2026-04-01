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
        $ownerRole = Role::where('slug', 'OWNER')->first();
        $managerRole = Role::where('slug', 'MANAGER')->first();
        $cashierRole = Role::where('slug', 'CASHIER')->first();
        $waiterRole = Role::where('slug', 'WAITER')->first();
        $kitchenRole = Role::where('slug', 'KITCHEN')->first();

        $users = [
            [
                'is_active' => true,
                'user_name' => 'owner',
                'full_name' => 'Chu Nha Hang',
                'email' => 'owner@restaurant.com',
                'password' => '12345678',
                'role_id' => $ownerRole?->id,
            ],
            [
                'is_active' => true,
                'user_name' => 'manager',
                'full_name' => 'Quan Ly Nha Hang',
                'email' => 'manager@restaurant.com',
                'password' => '12345678',
                'role_id' => $managerRole?->id,
            ],
            [
                'is_active' => true,
                'user_name' => 'cashier',
                'full_name' => 'Thu Ngan',
                'email' => 'cashier@restaurant.com',
                'password' => '12345678',
                'role_id' => $cashierRole?->id,
            ],
            [
                'is_active' => true,
                'user_name' => 'waiter',
                'full_name' => 'Nhan Vien Phuc Vu Ban',
                'email' => 'waiter@restaurant.com',
                'password' => '12345678',
                'role_id' => $waiterRole?->id,
            ],
            [
                'is_active' => true,
                'user_name' => 'kitchen',
                'full_name' => 'Nhan Vien Nha Bep',
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

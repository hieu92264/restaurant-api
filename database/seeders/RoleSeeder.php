<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'is_active' => true,
                'name' => 'Chủ nhà hàng',
                'code' => 'OWNER',
                'remark' => 'Toàn quyền hệ thống',
            ],
            [
                'is_active' => true,
                'name' => 'Quản lý',
                'code' => 'MANAGER',
                'remark' => 'Quản lý vận hành nhà hàng',
            ],
            [
                'is_active' => true,
                'name' => 'Thu ngân',
                'code' => 'CASHIER',
                'remark' => 'Quản lý thanh toán hóa đơn',
            ],
            [
                'is_active' => true,
                'name' => 'Nhân viên phục vụ bàn',
                'code' => 'WAITER',
                'remark' => 'Phục vụ bàn và hỗ trợ khách',
            ],
            [
                'is_active' => true,
                'name' => 'Nhà bếp',
                'code' => 'KITCHEN',
                'remark' => 'Chuẩn bị món ăn',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}

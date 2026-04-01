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
                'slug' => 'OWNER',
                'remark' => 'Toàn quyền hệ thống',
            ],
            [
                'is_active' => true,
                'name' => 'Quản lý',
                'slug' => 'MANAGER',
                'remark' => 'Quản lý vận hành nhà hàng',
            ],
            [
                'is_active' => true,
                'name' => 'Thu ngân',
                'slug' => 'CASHIER',
                'remark' => 'Quản lý thanh toán hóa đơn',
            ],
            [
                'is_active' => true,
                'name' => 'Nhân viên phục vụ bàn',
                'slug' => 'WAITER',
                'remark' => 'Phục vụ bàn và hỗ trợ khách',
            ],
            [
                'is_active' => true,
                'name' => 'Nhà bếp',
                'slug' => 'KITCHEN',
                'remark' => 'Chuẩn bị món ăn',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}

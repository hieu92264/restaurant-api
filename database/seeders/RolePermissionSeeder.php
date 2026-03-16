<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = Role::where('code', 'OWNER')->first();
        $manager = Role::where('code', 'MANAGER')->first();
        $cashier = Role::where('code', 'CASHIER')->first();
        $waiter = Role::where('code', 'WAITER')->first();
        $kitchen = Role::where('code', 'KITCHEN')->first();

        $allPermissionIds = Permission::pluck('id')->toArray();

        $managerPermissionIds = Permission::whereIn('code', [
            'USER_READ',
            'USER_CREATE',
            'USER_UPDATE',
            'ROLE_READ',
            'PERMISSION_READ',
        ])->pluck('id')->toArray();

        $cashierPermissionIds = Permission::whereIn('code', [
            'USER_READ',
        ])->pluck('id')->toArray();

        $waiterPermissionIds = Permission::whereIn('code', [
            'USER_READ',
        ])->pluck('id')->toArray();

        $kitchenPermissionIds = Permission::whereIn('code', [
            'USER_READ',
        ])->pluck('id')->toArray();

        if ($owner) {
            $owner->permissions()->sync($allPermissionIds);
        }

        if ($manager) {
            $manager->permissions()->sync($managerPermissionIds);
        }

        if ($cashier) {
            $cashier->permissions()->sync($cashierPermissionIds);
        }

        if ($waiter) {
            $waiter->permissions()->sync($waiterPermissionIds);
        }

        if ($kitchen) {
            $kitchen->permissions()->sync($kitchenPermissionIds);
        }
    }
}

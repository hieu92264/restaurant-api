<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent: user
        $userParent = Permission::updateOrCreate(
            ['code' => 'USER'],
            [
                'is_active' => 'Y',
                'name' => 'User',
                'code' => 'USER',
                'remark' => 'Nhóm quyền người dùng',
                'url' => '#',
                'parent_id' => null,
            ]
        );

        // CRUD user (parent = user)
        $userPermissions = [
            [
                'name' => 'User Create',
                'code' => 'USER_CREATE',
                'remark' => 'Tạo người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'User Read',
                'code' => 'USER_READ',
                'remark' => 'Xem người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'User Update',
                'code' => 'USER_UPDATE',
                'remark' => 'Cập nhật người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'User Delete',
                'code' => 'USER_DELETE',
                'remark' => 'Xóa người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
        ];

        foreach ($userPermissions as $permission) {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                array_merge(['is_active' => 'Y'], $permission)
            );
        }

        // CRUD role (theo yêu cầu: parent = user)
        $rolePermissions = [
            [
                'name' => 'Role Create',
                'code' => 'ROLE_CREATE',
                'remark' => 'Tạo vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'Role Read',
                'code' => 'ROLE_READ',
                'remark' => 'Xem vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'Role Update',
                'code' => 'ROLE_UPDATE',
                'remark' => 'Cập nhật vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'Role Delete',
                'code' => 'ROLE_DELETE',
                'remark' => 'Xóa vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
        ];

        foreach ($rolePermissions as $permission) {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                array_merge(['is_active' => 'Y'], $permission)
            );
        }

        // Parent: permission
        $permissionParent = Permission::updateOrCreate(
            ['code' => 'PERMISSION'],
            [
                'is_active' => 'Y',
                'name' => 'Permission',
                'code' => 'PERMISSION',
                'remark' => 'Nhóm quyền phân quyền',
                'url' => '#',
                'parent_id' => null,
            ]
        );

        // CRUD permission (parent = permission)
        $permissionCrud = [
            [
                'name' => 'Permission Create',
                'code' => 'PERMISSION_CREATE',
                'remark' => 'Tạo quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
            [
                'name' => 'Permission Read',
                'code' => 'PERMISSION_READ',
                'remark' => 'Xem quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
            [
                'name' => 'Permission Update',
                'code' => 'PERMISSION_UPDATE',
                'remark' => 'Cập nhật quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
            [
                'name' => 'Permission Delete',
                'code' => 'PERMISSION_DELETE',
                'remark' => 'Xóa quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
        ];

        foreach ($permissionCrud as $permission) {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                array_merge(['is_active' => 'Y'], $permission)
            );
        }
    }
}

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
        $userParent = Permission::updateOrCreate(
            ['slug' => 'USER'],
            [
                'is_active' => true,
                'name' => 'User',
                'slug' => 'USER',
                'remark' => 'Nhóm quyền người dùng',
                'url' => '#',
                'parent_id' => null,
            ]
        );

        $userPermissions = [
            [
                'name' => 'User Create',
                'slug' => 'USER_CREATE',
                'remark' => 'Tạo người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'User Read',
                'slug' => 'USER_READ',
                'remark' => 'Xem người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'User Update',
                'slug' => 'USER_UPDATE',
                'remark' => 'Cập nhật người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'User Delete',
                'slug' => 'USER_DELETE',
                'remark' => 'Xóa người dùng',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
        ];

        foreach ($userPermissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                array_merge(['is_active' => true], $permission)
            );
        }

        $rolePermissions = [
            [
                'name' => 'Role Create',
                'slug' => 'ROLE_CREATE',
                'remark' => 'Tạo vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'Role Read',
                'slug' => 'ROLE_READ',
                'remark' => 'Xem vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'Role Update',
                'slug' => 'ROLE_UPDATE',
                'remark' => 'Cập nhật vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
            [
                'name' => 'Role Delete',
                'slug' => 'ROLE_DELETE',
                'remark' => 'Xóa vai trò',
                'url' => '#',
                'parent_id' => $userParent->id,
            ],
        ];

        foreach ($rolePermissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                array_merge(['is_active' => true], $permission)
            );
        }

        $permissionParent = Permission::updateOrCreate(
            ['slug' => 'PERMISSION'],
            [
                'is_active' => true,
                'name' => 'Permission',
                'slug' => 'PERMISSION',
                'remark' => 'Nhóm quyền phân quyền',
                'url' => '#',
                'parent_id' => null,
            ]
        );

        $permissionCrud = [
            [
                'name' => 'Permission Create',
                'slug' => 'PERMISSION_CREATE',
                'remark' => 'Tạo quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
            [
                'name' => 'Permission Read',
                'slug' => 'PERMISSION_READ',
                'remark' => 'Xem quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
            [
                'name' => 'Permission Update',
                'slug' => 'PERMISSION_UPDATE',
                'remark' => 'Cập nhật quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
            [
                'name' => 'Permission Delete',
                'slug' => 'PERMISSION_DELETE',
                'remark' => 'Xóa quyền',
                'url' => '#',
                'parent_id' => $permissionParent->id,
            ],
        ];

        foreach ($permissionCrud as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                array_merge(['is_active' => true], $permission)
            );
        }
    }
}

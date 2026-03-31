<?php

namespace Tests\Feature;

use App\Http\Requests\StoreRoleRequest;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('roles'));

        $this->assertTrue(Schema::hasColumns('roles', [
            'id',
            'is_active',
            'name',
            'code',
            'remark',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_role_can_be_created_with_default_is_active_value(): void
    {
        $role = Role::query()->create([
            'name' => 'Manager',
            'code' => 'MANAGER',
            'remark' => 'System manager role',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'is_active' => true,
            'name' => 'Manager',
            'code' => 'MANAGER',
        ]);
    }

    public function test_store_role_request_requires_unique_name_and_code(): void
    {
        Role::query()->create([
            'name' => 'Manager',
            'code' => 'MANAGER',
            'remark' => 'Existing role',
        ]);

        $validator = Validator::make(
            [
                'name' => 'Manager',
                'code' => 'MANAGER',
                'remark' => 'Duplicate role',
            ],
            (new StoreRoleRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('code', $validator->errors()->toArray());
    }
}

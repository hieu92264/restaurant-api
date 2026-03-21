<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()->get();

        return $this->success($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        return $this->success($role, 'Tạo vai trò thành công.', Response::HTTP_CREATED);
    }

    public function show(Role $role): JsonResponse
    {
        return $this->success($role);
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = Role::query()->findOrFail($id);
        $role->update($request->validated());

        return $this->success($role->fresh(), 'Cập nhật vai trò thành công.');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::query()->findOrFail($id);
        $role->update([
            'is_active' => 'N',
        ]);

        return $this->success(null, 'Xoá vai trò thành công.');
    }
}

<?php

namespace App\Http\Controllers\Api\Auth;

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

    public function show(string $slug): JsonResponse
    {
        $role = Role::query()->where('slug', $slug)->firstOrFail();

        return $this->success($role);
    }

    public function destroy(string $slug): JsonResponse
    {
        $role = Role::query()->where('slug', $slug)->firstOrFail();

        $role->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Xóa vai trò thành công.');
    }

    public function update(UpdateRoleRequest $request, string $slug): JsonResponse
    {
        $role = Role::query()->where('slug', $slug)->firstOrFail();

        $role->update($request->validated());

        return $this->success($role->fresh(), 'Cập nhật vai trò thành công.');
    }
}

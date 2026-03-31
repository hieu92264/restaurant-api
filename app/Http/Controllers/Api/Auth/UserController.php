<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with('role')
            ->get();

        return $this->success($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return $this->success(
            $user->load('role'),
            'Tạo tài khoản thành công.',
            Response::HTTP_CREATED
        );
    }

    public function show(User $user): JsonResponse
    {
        return $this->success($user->load('role'));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);
        $user->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Xoá tài khoản thành công.');
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);
        $user->update($request->validated());

        return $this->success($user->fresh()->load('role'), 'Cập nhật tài khoản thành công.');
    }
}

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $field = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';

        if (! $token = auth('api')->attempt([
            $field => $data['username'],
            'password' => $data['password'],
            'is_active' => true,
        ])) {
            return $this->error(null, 'Tên đăng nhập hoặc mật khẩu không đúng.', Response::HTTP_FORBIDDEN);
        }

        return $this->responseWithToken($token);
    }

    protected function responseWithToken(string $token): JsonResponse
    {
        return $this->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return $this->success(null, 'Đăng xuất thành công.');
    }

    public function refresh(): JsonResponse
    {
        return $this->responseWithToken(auth('api')->refresh());
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user()->load([
            'role',
        ]);

        return $this->success([
            'id' => $user->id,
            'user_name' => $user->user_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => [
                'id' => $user->role?->id,
                'name' => $user->role?->name,
                'code' => $user->role?->code,
                'remark' => $user->role?->remark,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $field = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';

        if (!$token = auth('api')->attempt([
            $field => $data['username'],
            'password' => $data['password'],
            'is_active' => 'Y',
        ])) {
            return $this->error(null, 'Unauthorized', 401);
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

        return $this->success(null, 'Successfully logged out');
    }

    public function refresh(): JsonResponse
    {
        return $this->responseWithToken(auth('api')->refresh());
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user()->load([
            'role',
            //            'role.permissions' => function ($query) {
            //                $query->where('is_active', 'Y')
            //                    ->select(
            //                        'permissions.id',
            //                        'permissions.name',
            //                        'permissions.code',
            //                        'permissions.remark',
            //                        'permissions.url',
            //                        'permissions.parent_id'
            //                    )
            //                    ->orderBy('permissions.parent_id')
            //                    ->orderBy('permissions.id');
            //            },
        ]);

        //        $permissionTree = $this->buildPermissionTree($user->role?->permissions ?? collect());

        return $this->success([
            'id' => $user->id,
            'user_name' => $user->user_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => [
                'id' => $user->role?->id,
                'name' => $user->role?->name,
                'code' => $user->role?->code,
                'remark' => $user->role?->remark,
            ],
            //            'permissions' => $permissionTree,
        ]);
    }

    protected function buildPermissionTree($permissions): array
    {
        $grouped = $permissions->groupBy('parent_id');

        $build = function ($parentId) use (&$build, $grouped) {
            return ($grouped[$parentId] ?? collect())
                ->map(function ($permission) use (&$build) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'code' => $permission->code,
                        'url' => $permission->url,
                        'remark' => $permission->remark,
                        'children' => $build($permission->id),
                    ];
                })
                ->toArray();
        };

        return $build(null);
    }
}

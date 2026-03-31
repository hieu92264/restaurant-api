<?php

namespace App\Http\Middleware;

use App\Common\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, string $roles = ''): Response
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error(null, 'Bạn chưa đăng nhập.', Response::HTTP_UNAUTHORIZED);
        }

        [$allowPart, $denyPart] = array_pad(explode('|', $roles, 2), 2, '');

        $allowRoles = collect(explode(',', $allowPart))
            ->map(fn ($role) => trim($role))
            ->filter()
            ->toArray();

        $denyRoles = collect(explode(',', $denyPart))
            ->map(fn ($role) => trim($role))
            ->filter()
            ->toArray();

        $currentRole = $user->role?->code;

        if (!$currentRole) {
            return $this->error([
                'role' => ['Tài khoản chưa được gán vai trò.'],
            ], 'Bạn không có quyền truy cập.', Response::HTTP_FORBIDDEN);
        }

        if (!empty($denyRoles) && in_array($currentRole, $denyRoles, true)) {
            return $this->error([
                'role' => ["Vai trò {$currentRole} không được phép truy cập tuyến này."],
            ], 'Bạn không có quyền truy cập.', Response::HTTP_FORBIDDEN);
        }

        if ($currentRole === 'OWNER') {
            return $next($request);
        }

        if (!empty($allowRoles) && !in_array($currentRole, $allowRoles, true)) {
            return $this->error([
                'role' => ["Vai trò {$currentRole} không được phép truy cập tuyến này."],
            ], 'Bạn không có quyền truy cập.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

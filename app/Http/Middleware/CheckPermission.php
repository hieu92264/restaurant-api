<?php

namespace App\Http\Middleware;

use App\Common\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $permissionCode): Response
    {
        if (!$request->user() || !$request->user()->hasPermission($permissionCode)) {
            return $this->error([
                'message' => 'Ban khong co quyen truy cap URL nay.',
            ], 'Forbidden', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

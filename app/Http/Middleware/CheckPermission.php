<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $permissionCode): Response
    {
        if (!$request->user() || !$request->user()->hasPermission($permissionCode)) {
            return response()->json([
                'data' => null,
                'message' => 'Forbidden',
                'errors' => ['message' => 'Bạn không có quyền truy cập URL này.'],
                'meta' => [
                    'uri' => $request->fullUrl(),
                    'timestamp' => now()->toDateTimeString(),
                ],
            ], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}

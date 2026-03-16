<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles = ''): Response
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => null,
                'meta' => [
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        [$allowPart, $denyPart] = array_pad(explode('|', $roles, 2), 2, '');

        $allowRoles = collect(explode(',', $allowPart))
            ->map(fn($role) => trim($role))
            ->filter()
            ->values()
            ->toArray();

        $denyRoles = collect(explode(',', $denyPart))
            ->map(fn($role) => trim($role))
            ->filter()
            ->values()
            ->toArray();

        $currentRole = $user->role?->code;

        if (!$currentRole) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'errors' => [
                    'role' => ['User does not have a role assigned.'],
                ],
                'meta' => [
                    'status_code' => Response::HTTP_FORBIDDEN,
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        if (!empty($denyRoles) && in_array($currentRole, $denyRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'errors' => [
                    'role' => ["Role {$currentRole} is denied for this route."],
                ],
                'meta' => [
                    'status_code' => Response::HTTP_FORBIDDEN,
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        if ($currentRole === 'OWNER') {
            return $next($request);
        }

        if (!empty($allowRoles) && !in_array($currentRole, $allowRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'errors' => [
                    'role' => ["Role {$currentRole} is not allowed for this route."],
                ],
                'meta' => [
                    'status_code' => Response::HTTP_FORBIDDEN,
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

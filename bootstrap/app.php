<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
//        api: __DIR__.'/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            $apiPrefix = config('app.api_prefix');
            $routesPath = base_path('routes');
            $apiFiles = array_filter(
                glob($routesPath . '/*.php'),
                fn($file) => !in_array(basename($file), ['web.php', 'console.php'])
            );

            foreach ($apiFiles as $file) {
                Route::prefix($apiPrefix)
                    ->middleware('api')
                    ->group($file);
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if (!$request->is('api/*')) {
                return $response;
            }

            $statusCode = match (true) {
                $e instanceof ValidationException => 422,
                $e instanceof AuthenticationException => 401,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => $response->getStatusCode() && $response->getStatusCode() !== 200
                    ? $response->getStatusCode()
                    : 500,
            };

            $message = match (true) {
                $e instanceof ValidationException => 'Validation failed',
                $e instanceof AuthenticationException => 'Unauthenticated',
                default => $e->getMessage() ?: 'Server Error',
            };

            $payload = [
                'success' => false,
                'message' => $message,
                'errors' => null,
                'meta' => [
                    'status_code' => $statusCode,
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ],
            ];

            if ($e instanceof ValidationException) {
                $payload['errors'] = $e->errors();
            }

            if (app()->isLocal() || config('app.debug')) {
                $payload['debug'] = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            return response()->json($payload, $statusCode);
        });
    })
    ->create();

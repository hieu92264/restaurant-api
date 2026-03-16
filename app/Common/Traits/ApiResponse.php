<?php

namespace App\Common\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            [
                'data' => $data,
                'message' => $message,
                'meta' => [
                    'uri' => request()->fullUrl(),
                    'timestamp' => now()->toDateTimeString()
                ]
            ], $statusCode
        );
    }

    protected function error(
        mixed  $data = null,
        string $message = 'Error',
        int    $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse
    {
        $payload = [
            'data' => null,
            'message' => $message,
            'errors' => null,
            'meta' => [
                'uri' => request()->fullUrl(),
                'timestamp' => now()->toDateTimeString(),
            ],
        ];

        if ($data instanceof Throwable) {
            $payload['errors'] = app()->isLocal() || config('app.debug')
                ? [
                    'exception' => get_class($data),
                    'message' => $data->getMessage(),
                    'file' => $data->getFile(),
                    'line' => $data->getLine(),
                ]
                : ['message' => 'Internal Server Error'];
        } else {
            $payload['errors'] = $data;
        }

        return response()->json($payload, $statusCode);
    }
}

<?php

namespace App\Common\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ApiResponse
{
    protected function apiResponse(
        mixed $metadata = null,
        string $message = 'Thành công.',
        int $statusCode = Response::HTTP_OK,
        ?string $stack = null
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'statusCode' => $statusCode,
            'metadata' => $metadata,
            'path' => request()->getPathInfo(),
            'timestamp' => now()->toISOString(),
        ];

        if ($stack !== null) {
            $payload['stack'] = $stack;
        }

        return response()->json($payload, $statusCode);
    }

    protected function success(
        mixed $data = null,
        string $message = 'Thành công.',
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return $this->apiResponse($data, $message, $statusCode);
    }

    protected function error(
        mixed $data = null,
        string $message = 'Có lỗi xảy ra.',
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        $metadata = null;
        $stack = null;

        if ($data instanceof Throwable) {
            if (app()->isLocal() || config('app.debug')) {
                $metadata = [
                    'exception' => get_class($data),
                    'message' => $data->getMessage(),
                    'file' => $data->getFile(),
                    'line' => $data->getLine(),
                ];
                $stack = $data->getTraceAsString();
            }
        } else {
            $metadata = $data;
        }

        return $this->apiResponse($metadata, $message, $statusCode, $stack);
    }
}

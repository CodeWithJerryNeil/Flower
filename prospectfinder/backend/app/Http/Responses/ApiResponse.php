<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The single JSON envelope used by every API endpoint.
 *
 *   success: { success: true,  message, data }
 *   error:   { success: false, message, errors }
 */
final class ApiResponse
{
    /** @param  array<string, string>  $headers */
    public static function success(
        mixed $data = null,
        string $message = 'Request completed.',
        int $status = 200,
        array $headers = [],
    ): JsonResponse {
        if ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status, $headers);
    }

    /**
     * @param  array<string, mixed>  $errors
     * @param  array<string, string>  $headers
     */
    public static function error(
        string $message,
        int $status,
        array $errors = [],
        array $headers = [],
    ): JsonResponse {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
        ], $status, $headers);
    }
}

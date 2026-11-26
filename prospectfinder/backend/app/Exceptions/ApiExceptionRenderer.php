<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Converts exceptions on API routes into the standard error envelope.
 * Internal error details are never included in responses (they are still logged).
 */
final class ApiExceptionRenderer
{
    private const MESSAGES = [
        401 => 'Unauthenticated.',
        403 => 'This action is unauthorized.',
        404 => 'Resource not found.',
        405 => 'Method not allowed.',
        419 => 'Session expired.',
        429 => 'Too many requests. Please try again later.',
    ];

    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => ApiResponse::error('Validation failed.', $e->status, $e->errors()),
            $e instanceof AuthenticationException => ApiResponse::error(self::MESSAGES[401], 401),
            $e instanceof HttpExceptionInterface => $this->http($e),
            // In local debug mode let Laravel render the full exception for developers.
            config('app.debug') => null,
            default => ApiResponse::error('Server error.', 500),
        };
    }

    private function http(HttpExceptionInterface $e): JsonResponse
    {
        $status = $e->getStatusCode();

        // Policy denials may carry a deliberate, user-facing message.
        $message = $status === 403 && $e->getMessage() !== '' && $e->getMessage() !== 'This action is unauthorized.'
            ? $e->getMessage()
            : (self::MESSAGES[$status] ?? ($status >= 500 ? 'Server error.' : 'Request could not be completed.'));

        return ApiResponse::error($message, $status, headers: $e->getHeaders());
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Reports whether the API and its core infrastructure (database, Redis) are reachable.
 * Exposes only up/down booleans, never connection details.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::connection()->getPdo()),
            'redis' => $this->check(fn () => Redis::connection()->ping()),
        ];

        $healthy = ! in_array(false, $checks, true);

        // A 503 still carries the checks so callers can see which service is down.
        return $healthy
            ? ApiResponse::success(['checks' => $checks], 'All systems operational.')
            : response()->json([
                'success' => false,
                'message' => 'One or more services are unavailable.',
                'data' => ['checks' => $checks],
            ], 503);
    }

    private function check(callable $probe): bool
    {
        try {
            $probe();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}

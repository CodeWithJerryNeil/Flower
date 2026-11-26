<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_reports_database_and_redis_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['checks' => ['database' => true, 'redis' => true]],
            ]);
    }

    public function test_health_endpoint_returns_503_when_redis_is_down(): void
    {
        Redis::shouldReceive('connection')->andThrow(new \RuntimeException('down'));

        $this->getJson('/api/v1/health')
            ->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.checks.redis', false)
            ->assertJsonPath('data.checks.database', true);
    }

    public function test_frontend_origin_is_allowed_by_cors(): void
    {
        $this->withHeaders(['Origin' => 'http://localhost:5173'])
            ->getJson('/api/v1/health')
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }

    public function test_unknown_origin_is_not_echoed_by_cors(): void
    {
        // With a single configured origin, the fixed frontend origin is returned,
        // which browsers reject for any other requesting origin.
        $response = $this->withHeaders(['Origin' => 'https://evil.example'])
            ->getJson('/api/v1/health');

        $this->assertNotSame('https://evil.example', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertNotSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }
}

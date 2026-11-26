<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorEnvelopeTest extends TestCase
{
    public function test_unknown_api_route_returns_404_envelope(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Resource not found.', 'errors' => []]);
    }

    public function test_wrong_method_returns_405_envelope(): void
    {
        $this->deleteJson('/api/v1/health')
            ->assertStatus(405)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Method not allowed.');
    }

    public function test_unexpected_errors_do_not_leak_details_when_debug_is_off(): void
    {
        config(['app.debug' => false]);
        Route::middleware('api')->get('/api/v1/_test/explode', fn () => throw new RuntimeException('db password is hunter2'));

        $this->getJson('/api/v1/_test/explode')
            ->assertStatus(500)
            ->assertExactJson(['success' => false, 'message' => 'Server error.', 'errors' => []])
            ->assertDontSee('hunter2');
    }

    public function test_non_api_routes_are_not_wrapped(): void
    {
        $this->get('/up')->assertOk();
    }
}

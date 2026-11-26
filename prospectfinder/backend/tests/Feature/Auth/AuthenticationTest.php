<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['email' => 'me@example.com', 'password' => 'correct-horse-battery']);
    }

    public function test_user_can_log_in_and_receives_a_bearer_token(): void
    {
        $user = $this->user();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ME@example.com',
            'password' => 'correct-horse-battery',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logged in.')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'me@example.com')
            ->assertJsonMissingPath('data.user.password');

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertNotNull($response->json('data.expires_at'));
        $this->assertNotNull($user->tokens()->first()->expires_at);
    }

    public function test_wrong_password_and_unknown_email_get_the_same_generic_error(): void
    {
        $this->user();

        $wrongPassword = $this->postJson('/api/v1/auth/login', ['email' => 'me@example.com', 'password' => 'nope']);
        $unknownEmail = $this->postJson('/api/v1/auth/login', ['email' => 'who@example.com', 'password' => 'nope']);

        foreach ([$wrongPassword, $unknownEmail] as $response) {
            $response->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonPath('message', 'Validation failed.')
                ->assertJsonPath('errors.email.0', 'These credentials do not match our records.');
        }
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_validates_input(): void
    {
        $this->postJson('/api/v1/auth/login', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_is_rate_limited(): void
    {
        $this->user();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'me@example.com', 'password' => 'wrong'])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'me@example.com', 'password' => 'correct-horse-battery'])
            ->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertHeader('Retry-After');
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = $this->user();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'me@example.com')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    }

    public function test_protected_routes_reject_missing_token_with_json_even_without_accept_header(): void
    {
        $this->get('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertExactJson(['success' => false, 'message' => 'Unauthenticated.', 'errors' => []]);
    }

    public function test_invalid_and_expired_tokens_are_rejected(): void
    {
        $user = $this->user();
        $expired = $user->createToken('old', ['*'], now()->subMinute())->plainTextToken;

        $this->withToken($expired)->getJson('/api/v1/auth/me')->assertStatus(401);
        $this->app['auth']->forgetGuards();
        $this->withToken('1|not-a-real-token')->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_logout_revokes_only_the_current_token(): void
    {
        $user = $this->user();
        $current = $user->createToken('current')->plainTextToken;
        $user->createToken('other-device');

        $this->withToken($current)->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out.');

        $this->assertSame(1, $user->tokens()->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($current)->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_session_cookies_do_not_authenticate_api_requests(): void
    {
        $this->actingAs($this->user(), 'web')->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_there_is_no_public_registration_endpoint(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'x', 'email' => 'x@example.com', 'password' => 'password12345',
        ])->assertNotFound()->assertJsonPath('success', false);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_passwords_are_stored_hashed(): void
    {
        $user = $this->user();

        $this->assertNotSame('correct-horse-battery', $user->getRawOriginal('password'));
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }
}

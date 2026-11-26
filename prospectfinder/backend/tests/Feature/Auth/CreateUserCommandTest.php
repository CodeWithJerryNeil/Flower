<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_with_a_hashed_password(): void
    {
        $this->artisan('prospectfinder:create-user', ['email' => ' Owner@Example.com ', '--name' => 'Owner'])
            ->expectsQuestion('Password', 'a-long-enough-password')
            ->expectsQuestion('Confirm password', 'a-long-enough-password')
            ->expectsOutput('User owner@example.com created.')
            ->assertSuccessful();

        $user = User::sole();
        $this->assertSame('owner@example.com', $user->email);
        $this->assertTrue(Hash::check('a-long-enough-password', $user->password));
    }

    public function test_it_rejects_short_or_mismatched_passwords(): void
    {
        $this->artisan('prospectfinder:create-user', ['email' => 'a@example.com', '--name' => 'A'])
            ->expectsQuestion('Password', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->assertFailed();

        $this->artisan('prospectfinder:create-user', ['email' => 'a@example.com', '--name' => 'A'])
            ->expectsQuestion('Password', 'a-long-enough-password')
            ->expectsQuestion('Confirm password', 'a-different-password')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_rejects_duplicate_emails(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->artisan('prospectfinder:create-user', ['email' => 'taken@example.com', '--name' => 'B'])
            ->expectsQuestion('Password', 'a-long-enough-password')
            ->expectsQuestion('Confirm password', 'a-long-enough-password')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }
}

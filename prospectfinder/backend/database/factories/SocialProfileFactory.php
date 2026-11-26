<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\Business;
use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SocialProfile> */
class SocialProfileFactory extends Factory
{
    public function definition(): array
    {
        $username = 'example_'.fake()->unique()->userName();

        return [
            'business_id' => Business::factory(),
            'platform' => SocialPlatform::Instagram,
            'profile_url' => "https://www.instagram.com/{$username}/",
            'username' => $username,
            'source_url' => 'https://'.fake()->slug(2).'.example/',
            'confidence_score' => fake()->numberBetween(50, 100),
            'discovered_at' => now(),
        ];
    }

    public function facebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => SocialPlatform::Facebook,
            'profile_url' => "https://www.facebook.com/{$attributes['username']}",
        ]);
    }
}

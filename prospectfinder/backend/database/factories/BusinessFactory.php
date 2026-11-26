<?php

namespace Database\Factories;

use App\Enums\BusinessStatus;
use App\Enums\WebsiteStatus;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Test/demo data only. Uses the reserved .example TLD and fictional 555-01xx numbers.
 *
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'normalized_name' => Str::of($name)->lower()->squish()->toString(),
            'category' => fake()->randomElement(['Coffee shop', 'Bakery', 'Hair salon', 'Plumber', 'Florist']),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'country' => 'USA',
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'phone' => fake()->numerify('+1-212-555-01##'),
            'website_url' => null,
            'website_status' => WebsiteStatus::Unknown,
            'business_status' => BusinessStatus::Active,
        ];
    }

    public function withWebsite(): static
    {
        return $this->state(fn (array $attributes) => [
            'website_url' => 'https://'.Str::slug($attributes['name']).'.example',
            'website_status' => WebsiteStatus::HasWebsite,
        ]);
    }

    public function socialOnly(): static
    {
        return $this->state(fn () => ['website_status' => WebsiteStatus::SocialOnly]);
    }

    public function noWebsiteFound(): static
    {
        return $this->state(fn () => ['website_status' => WebsiteStatus::NoWebsiteFound]);
    }
}

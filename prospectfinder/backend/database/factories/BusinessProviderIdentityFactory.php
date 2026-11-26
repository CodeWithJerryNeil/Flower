<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessProviderIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BusinessProviderIdentity> */
class BusinessProviderIdentityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'provider' => 'fake_provider',
            'external_id' => fake()->unique()->uuid(),
        ];
    }
}

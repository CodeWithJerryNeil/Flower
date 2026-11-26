<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\ProspectSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProspectSource> */
class ProspectSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'provider' => 'fake_provider',
            'source_type' => 'discovery_api',
            'source_url' => 'https://'.fake()->slug(2).'.example/',
            'field_name' => 'name',
            'evidence' => null,
            'collected_at' => now(),
        ];
    }
}

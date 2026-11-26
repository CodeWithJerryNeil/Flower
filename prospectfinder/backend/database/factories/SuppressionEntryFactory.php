<?php

namespace Database\Factories;

use App\Models\SuppressionEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SuppressionEntry> */
class SuppressionEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'business_id' => null,
            'reason' => 'opted_out',
            'source' => 'manual',
        ];
    }
}

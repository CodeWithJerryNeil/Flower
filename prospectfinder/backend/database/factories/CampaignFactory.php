<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Campaign> */
class CampaignFactory extends Factory
{
    public function definition(): array
    {
        $city = fake()->city();
        $category = fake()->randomElement(['Coffee shops', 'Bakeries', 'Hair salons', 'Plumbers', 'Florists']);

        return [
            'user_id' => User::factory(),
            'name' => "{$city} {$category}",
            'location_query' => "{$city}, USA",
            'city' => $city,
            'state' => fake()->stateAbbr(),
            'country' => 'USA',
            'category' => $category,
            'target_count' => 10,
            'status' => CampaignStatus::Pending,
            'filters' => [],
        ];
    }

    public function status(CampaignStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}

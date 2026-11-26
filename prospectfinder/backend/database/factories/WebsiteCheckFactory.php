<?php

namespace Database\Factories;

use App\Enums\WebsiteStatus;
use App\Models\Business;
use App\Models\WebsiteCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WebsiteCheck> */
class WebsiteCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'checked_url' => null,
            'status' => WebsiteStatus::NoWebsiteFound,
            'http_status' => null,
            'is_social_domain' => false,
            'evidence' => ['note' => 'factory data'],
            'checked_at' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\OutreachChannel;
use App\Enums\OutreachStatus;
use App\Models\Business;
use App\Models\OutreachActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OutreachActivity> */
class OutreachActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'channel' => OutreachChannel::Email,
            'status' => OutreachStatus::Planned,
            'contacted_at' => null,
            'notes' => null,
        ];
    }
}

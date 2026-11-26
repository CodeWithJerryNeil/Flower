<?php

namespace Database\Factories;

use App\Enums\ResearchJobStatus;
use App\Enums\ResearchJobType;
use App\Models\Campaign;
use App\Models\ResearchJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ResearchJob> */
class ResearchJobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'business_id' => null,
            'job_type' => ResearchJobType::Discovery,
            'status' => ResearchJobStatus::Pending,
            'attempts' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\QualificationStatus;
use App\Models\Business;
use App\Models\Campaign;
use App\Models\CampaignLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CampaignLead> */
class CampaignLeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'business_id' => Business::factory(),
            'qualification_status' => QualificationStatus::Pending,
            'score' => null,
            'notes' => null,
        ];
    }
}

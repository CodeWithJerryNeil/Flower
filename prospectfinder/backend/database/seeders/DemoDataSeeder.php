<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Enums\QualificationStatus;
use App\Models\Business;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ProspectSource;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\WebsiteCheck;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fictional data for local UI development. Everything is labelled "[Demo]" and sourced
 * from provider "demo" so it cannot be confused with researched leads.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::query()->firstOrCreate(
                ['email' => 'demo@prospectfinder.test'],
                ['name' => 'Demo User', 'password' => 'password'],
            );

            $campaign = Campaign::factory()->for($user)->create([
                'name' => '[Demo] Coffee shops',
                'status' => CampaignStatus::Completed,
            ]);

            $businesses = collect([
                ...Business::factory()->count(4)->socialOnly()->create(),
                ...Business::factory()->count(3)->noWebsiteFound()->create(),
                ...Business::factory()->count(3)->withWebsite()->create(),
            ]);

            foreach ($businesses as $business) {
                $business->update([
                    'name' => '[Demo] '.$business->name,
                    'normalized_name' => '[demo] '.$business->normalized_name,
                ]);

                ProspectSource::factory()->for($business)->create(['provider' => 'demo']);
                WebsiteCheck::factory()->for($business)->create(['status' => $business->website_status]);

                $campaign->businesses()->attach($business, [
                    'qualification_status' => $business->website_url === null
                        ? QualificationStatus::Qualified->value
                        : QualificationStatus::Rejected->value,
                ]);
            }

            $businesses->take(4)->each(function (Business $business) {
                SocialProfile::factory()->for($business)->create();
                Contact::factory()->for($business)->create();
            });
        });
    }
}

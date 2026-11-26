<?php

namespace Tests\Feature\Database;

use App\Enums\CampaignStatus;
use App\Enums\ResearchJobStatus;
use App\Enums\WebsiteStatus;
use App\Models\Business;
use App\Models\Campaign;
use App\Models\ResearchJob;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ValueError;

class ConstraintsAndCastsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_records_get_safe_defaults(): void
    {
        $campaign = new Campaign([
            'name' => 'Corner cafes', 'location_query' => 'Austin, TX', 'country' => 'USA',
            'category' => 'Coffee shops', 'target_count' => 10,
        ]);
        $campaign->user()->associate(User::factory()->create())->save();
        $campaign->refresh();
        $business = Business::create(['name' => 'Corner Cafe', 'normalized_name' => 'corner cafe'])->fresh();

        $this->assertSame(CampaignStatus::Pending, $campaign->status);
        $this->assertSame([], $campaign->filters);
        $this->assertSame(WebsiteStatus::Unknown, $business->website_status);
        $this->assertNull($business->website_url);
        $this->assertNull($business->lead_score);
    }

    public function test_campaign_filters_round_trip_as_jsonb(): void
    {
        $filters = ['website_status' => ['no_website_found', 'social_only'], 'prioritize_email' => true];
        $campaign = Campaign::factory()->create(['filters' => $filters]);

        $this->assertSame($filters, $campaign->fresh()->filters);
        $this->assertSame(1, Campaign::whereJsonContains('filters->website_status', 'social_only')->count());
    }

    public function test_campaign_ownership_and_status_are_not_mass_assignable(): void
    {
        $campaign = new Campaign(['user_id' => 999, 'status' => 'completed', 'name' => 'X']);

        $this->assertNull($campaign->user_id);
        $this->assertSame(CampaignStatus::Pending, $campaign->status);
        $this->assertSame('X', $campaign->name);
    }

    public function test_invalid_enum_value_is_rejected(): void
    {
        $this->expectException(ValueError::class);
        Business::factory()->create(['website_status' => 'probably_no_website']);
    }

    public function test_target_count_must_be_positive(): void
    {
        $this->expectException(QueryException::class);
        Campaign::factory()->create(['target_count' => 0]);
    }

    public function test_lead_score_cannot_exceed_100(): void
    {
        $this->expectException(QueryException::class);
        Business::factory()->create(['lead_score' => 101]);
    }

    public function test_scopes(): void
    {
        $user = User::factory()->create();
        Campaign::factory()->for($user)->status(CampaignStatus::Researching)->create();
        Campaign::factory()->for($user)->status(CampaignStatus::Completed)->create();
        Campaign::factory()->status(CampaignStatus::Discovering)->create();

        Business::factory()->socialOnly()->create();
        Business::factory()->noWebsiteFound()->create();
        Business::factory()->withWebsite()->create();
        Business::factory()->create(['website_status' => WebsiteStatus::Inaccessible]);

        ResearchJob::factory()->create(['status' => ResearchJobStatus::Running]);
        ResearchJob::factory()->create(['status' => ResearchJobStatus::Failed]);

        $this->assertSame(2, Campaign::ownedBy($user)->count());
        $this->assertSame(2, Campaign::inProgress()->count());
        // Inaccessible is not treated as "no website".
        $this->assertSame(2, Business::withoutDedicatedWebsite()->count());
        $this->assertSame(1, Business::withWebsiteStatus(WebsiteStatus::Inaccessible)->count());
        $this->assertSame(1, ResearchJob::terminal()->count());
        $this->assertSame(1, ResearchJob::unfinished()->count());
    }

    public function test_terminal_states(): void
    {
        $this->assertFalse(CampaignStatus::Qualifying->isTerminal());
        $this->assertTrue(CampaignStatus::PartiallyCompleted->isTerminal());
        $this->assertFalse(ResearchJobStatus::Running->isTerminal());
        $this->assertTrue(ResearchJobStatus::Cancelled->isTerminal());
    }
}

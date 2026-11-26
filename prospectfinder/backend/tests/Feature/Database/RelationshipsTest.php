<?php

namespace Tests\Feature\Database;

use App\Enums\QualificationStatus;
use App\Models\Business;
use App\Models\Campaign;
use App\Models\CampaignLead;
use App\Models\Contact;
use App\Models\OutreachActivity;
use App\Models\ProspectSource;
use App\Models\ResearchJob;
use App\Models\SocialProfile;
use App\Models\User;
use App\Models\WebsiteCheck;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->for($user)->create();

        $this->assertTrue($campaign->user->is($user));
        $this->assertTrue($user->campaigns->first()->is($campaign));
    }

    public function test_business_has_multiple_contacts(): void
    {
        $business = Business::factory()->create();
        Contact::factory()->count(3)->for($business)->create();

        $this->assertCount(3, $business->contacts);
        $this->assertTrue($business->contacts->first()->business->is($business));
    }

    public function test_business_has_multiple_social_profiles(): void
    {
        $business = Business::factory()->create();
        SocialProfile::factory()->for($business)->create();
        SocialProfile::factory()->facebook()->for($business)->create();

        $this->assertCount(2, $business->socialProfiles);
    }

    public function test_business_belongs_to_multiple_campaigns(): void
    {
        $business = Business::factory()->create();
        [$first, $second] = Campaign::factory()->count(2)->create();

        $first->businesses()->attach($business, ['qualification_status' => 'qualified', 'score' => 80]);
        $second->businesses()->attach($business);

        $campaigns = $business->campaigns()->orderBy('campaigns.id')->get();
        $this->assertCount(2, $campaigns);
        $this->assertInstanceOf(CampaignLead::class, $campaigns[0]->lead);
        $this->assertSame(QualificationStatus::Qualified, $campaigns[0]->lead->qualification_status);
        $this->assertSame(80, $campaigns[0]->lead->score);
        $this->assertSame(QualificationStatus::Pending, $campaigns[1]->lead->qualification_status);
    }

    public function test_duplicate_campaign_business_relationship_is_rejected(): void
    {
        $campaign = Campaign::factory()->create();
        $business = Business::factory()->create();
        $campaign->businesses()->attach($business);

        $this->expectException(UniqueConstraintViolationException::class);
        $campaign->businesses()->attach($business);
    }

    public function test_campaign_has_leads_and_research_jobs(): void
    {
        $campaign = Campaign::factory()->create();
        CampaignLead::factory()->for($campaign)->count(2)->create();
        ResearchJob::factory()->for($campaign)->create();

        $this->assertCount(2, $campaign->leads);
        $this->assertCount(1, $campaign->researchJobs);
    }

    public function test_business_has_checks_sources_and_outreach(): void
    {
        $business = Business::factory()->create();
        WebsiteCheck::factory()->for($business)->create();
        ProspectSource::factory()->for($business)->create();
        OutreachActivity::factory()->for($business)->create();

        $this->assertCount(1, $business->websiteChecks);
        $this->assertCount(1, $business->prospectSources);
        $this->assertCount(1, $business->outreachActivities);
    }

    public function test_deleting_business_cascades_to_its_research_records(): void
    {
        $business = Business::factory()->create();
        Contact::factory()->for($business)->create();
        SocialProfile::factory()->for($business)->create();
        CampaignLead::factory()->for($business)->create();
        $job = ResearchJob::factory()->create(['business_id' => $business->id]);

        $business->delete();

        $this->assertDatabaseCount('contacts', 0);
        $this->assertDatabaseCount('social_profiles', 0);
        $this->assertDatabaseCount('campaign_leads', 0);
        // Job history is kept, detached from the deleted business.
        $this->assertNull($job->fresh()->business_id);
    }
}

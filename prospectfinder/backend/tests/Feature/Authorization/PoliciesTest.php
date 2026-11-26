<?php

namespace Tests\Feature\Authorization;

use App\Models\Business;
use App\Models\Campaign;
use App\Models\CampaignLead;
use App\Models\OutreachActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PoliciesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_owner_can_act_on_a_campaign(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $campaign = Campaign::factory()->for($owner)->create();

        foreach (['view', 'update', 'delete', 'start', 'cancel'] as $ability) {
            $this->assertTrue($owner->can($ability, $campaign), "owner {$ability}");
            $this->assertFalse($other->can($ability, $campaign), "other {$ability}");
        }

        $this->assertSame(404, Gate::forUser($other)->inspect('view', $campaign)->status());
    }

    public function test_only_the_campaign_owner_can_act_on_its_leads(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $lead = CampaignLead::factory()->for(Campaign::factory()->for($owner))->create();

        foreach (['view', 'update', 'research'] as $ability) {
            $this->assertTrue($owner->can($ability, $lead));
            $this->assertFalse($other->can($ability, $lead));
        }
    }

    public function test_outreach_access_follows_business_reachability(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $business = Business::factory()->create();
        Campaign::factory()->for($owner)->create()->businesses()->attach($business);
        $activity = OutreachActivity::factory()->for($business)->create();

        $this->assertTrue($owner->can('update', $activity));
        $this->assertTrue($owner->can('delete', $activity));
        $this->assertFalse($other->can('update', $activity));
        $this->assertFalse($other->can('delete', $activity));
    }

    public function test_businesses_are_scoped_to_users_through_campaigns(): void
    {
        $owner = User::factory()->create();
        $mine = Business::factory()->create();
        Business::factory()->create();
        Campaign::factory()->for($owner)->create()->businesses()->attach($mine);
        Campaign::factory()->for($owner)->create()->businesses()->attach($mine);

        $this->assertSame([$mine->id], Business::accessibleBy($owner)->pluck('id')->all());
        $this->assertSame(0, Business::accessibleBy(User::factory()->create())->count());
    }
}

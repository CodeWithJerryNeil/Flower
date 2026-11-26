<?php

namespace Tests\Feature\Api;

use App\Http\Resources\BusinessResource;
use App\Http\Resources\CampaignResource;
use App\Http\Responses\ApiResponse;
use App\Models\Business;
use App\Models\BusinessProviderIdentity;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ProspectSource;
use App\Models\SocialProfile;
use App\Models\WebsiteCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_resource_shape(): void
    {
        $campaign = Campaign::factory()->create(['filters' => []])->loadCount('leads');

        $data = ApiResponse::success(new CampaignResource($campaign))->getData(true)['data'];

        $this->assertSame(['query', 'city', 'state', 'country'], array_keys($data['location']));
        $this->assertSame('pending', $data['status']);
        $this->assertSame([], $data['filters']);
        $this->assertSame(0, $data['leads_count']);
        $this->assertArrayNotHasKey('user_id', $data);
    }

    public function test_business_resource_hides_internal_fields_and_includes_loaded_relations(): void
    {
        $business = Business::factory()->socialOnly()->create(['latitude' => 40.7128, 'longitude' => -74.006]);
        BusinessProviderIdentity::factory()->for($business)->create();
        Contact::factory()->for($business)->create();
        SocialProfile::factory()->for($business)->create();
        WebsiteCheck::factory()->for($business)->create();
        ProspectSource::factory()->for($business)->create();

        $data = (new BusinessResource($business->load(['contacts', 'socialProfiles', 'websiteChecks', 'prospectSources'])))
            ->response()->getData(true)['data'];

        $this->assertArrayNotHasKey('normalized_name', $data);
        $this->assertArrayNotHasKey('provider_identities', $data);
        $this->assertSame('social_only', $data['website_status']);
        $this->assertSame(40.7128, $data['latitude']);
        $this->assertCount(1, $data['contacts']);
        $this->assertArrayHasKey('verification_status', $data['contacts'][0]);
        $this->assertArrayHasKey('source_url', $data['contacts'][0]);
        $this->assertCount(1, $data['social_profiles']);
        $this->assertCount(1, $data['website_checks']);
        $this->assertCount(1, $data['sources']);
    }

    public function test_business_resource_omits_unloaded_relations(): void
    {
        $data = (new BusinessResource(Business::factory()->create()))->response()->getData(true)['data'];

        $this->assertArrayNotHasKey('contacts', $data);
        $this->assertArrayNotHasKey('sources', $data);
    }
}

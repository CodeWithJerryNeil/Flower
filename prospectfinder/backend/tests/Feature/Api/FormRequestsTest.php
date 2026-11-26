<?php

namespace Tests\Feature\Api;

use App\Enums\CampaignStatus;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\StoreOutreachActivityRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Campaign;
use App\Models\CampaignLead;
use App\Models\SuppressionEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Exercises the Form Requests through test-only routes. Real endpoints arrive in Phase 8.
 */
class FormRequestsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1/_test')->group(function () {
            Route::post('campaigns', fn (StoreCampaignRequest $r) => ApiResponse::success($r->campaignAttributes()));
            Route::patch('campaigns/{campaign}', fn (UpdateCampaignRequest $r, Campaign $campaign) => ApiResponse::success($r->campaignAttributes()));
            Route::patch('leads/{lead}', fn (UpdateLeadRequest $r, CampaignLead $lead) => ApiResponse::success($r->validated()));
            Route::post('leads/{lead}/outreach', fn (StoreOutreachActivityRequest $r, CampaignLead $lead) => ApiResponse::success($r->validated()));
        });

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @return array<string, mixed> */
    private function validCampaign(array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => 'NYC Coffee Shops',
            'location' => ['city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            'category' => 'Coffee shops',
            'target_count' => 10,
            'filters' => ['website_status' => ['no_website_found', 'social_only'], 'prioritize_email' => true],
            'research_mode' => 'automatic',
        ], $overrides);
    }

    public function test_store_campaign_accepts_the_documented_payload_and_maps_columns(): void
    {
        $this->postJson('/api/v1/_test/campaigns', $this->validCampaign())
            ->assertOk()
            ->assertJsonPath('data', [
                'name' => 'NYC Coffee Shops',
                'category' => 'Coffee shops',
                'target_count' => 10,
                'filters' => ['website_status' => ['no_website_found', 'social_only'], 'prioritize_email' => true],
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'location_query' => 'New York, NY, USA',
            ]);
    }

    public function test_store_campaign_accepts_free_text_location(): void
    {
        $payload = $this->validCampaign();
        $payload['location'] = ['query' => 'Brooklyn, NY', 'country' => 'USA'];

        $this->postJson('/api/v1/_test/campaigns', $payload)
            ->assertOk()
            ->assertJsonPath('data.location_query', 'Brooklyn, NY')
            ->assertJsonPath('data.city', null);
    }

    public function test_store_campaign_enforces_mvp_target_limits(): void
    {
        foreach ([0, 11, 'ten'] as $count) {
            $this->postJson('/api/v1/_test/campaigns', $this->validCampaign(['target_count' => $count]))
                ->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonValidationErrors('target_count');
        }
    }

    public function test_store_campaign_requires_core_fields(): void
    {
        $this->postJson('/api/v1/_test/campaigns', ['location' => ['state' => 'NY']])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name', 'category', 'target_count', 'location.country', 'location.city', 'location.query',
            ]);
    }

    public function test_store_campaign_rejects_unknown_filters_and_values(): void
    {
        $this->postJson('/api/v1/_test/campaigns', $this->validCampaign([
            'filters' => ['website_status' => ['maybe'], 'scrape_everything' => true],
            'research_mode' => 'aggressive',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['filters', 'filters.website_status.0', 'research_mode']);
    }

    public function test_update_campaign_allows_renaming_a_running_campaign(): void
    {
        $campaign = Campaign::factory()->for($this->user)->status(CampaignStatus::Researching)->create();

        $this->patchJson("/api/v1/_test/campaigns/{$campaign->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data', ['name' => 'Renamed']);
    }

    public function test_update_campaign_freezes_settings_after_start(): void
    {
        $campaign = Campaign::factory()->for($this->user)->status(CampaignStatus::Researching)->create();

        $this->patchJson("/api/v1/_test/campaigns/{$campaign->id}", ['category' => 'Bakeries', 'target_count' => 5])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category', 'target_count']);
    }

    public function test_update_campaign_allows_settings_while_pending(): void
    {
        $campaign = Campaign::factory()->for($this->user)->create();

        $this->patchJson("/api/v1/_test/campaigns/{$campaign->id}", ['category' => 'Bakeries'])
            ->assertOk()
            ->assertJsonPath('data', ['category' => 'Bakeries']);
    }

    public function test_requests_for_another_users_records_return_404(): void
    {
        $foreignCampaign = Campaign::factory()->create();
        $foreignLead = CampaignLead::factory()->create();

        $this->patchJson("/api/v1/_test/campaigns/{$foreignCampaign->id}", ['name' => 'x'])->assertNotFound();
        $this->patchJson("/api/v1/_test/leads/{$foreignLead->id}", ['notes' => 'x'])->assertNotFound();
        $this->postJson("/api/v1/_test/leads/{$foreignLead->id}/outreach", ['channel' => 'email', 'status' => 'planned'])
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_update_lead_validates_business_edits(): void
    {
        $lead = CampaignLead::factory()->for(Campaign::factory()->for($this->user))->create();

        $this->patchJson("/api/v1/_test/leads/{$lead->id}", [
            'qualification_status' => 'qualified',
            'notes' => 'Great fit.',
            'business' => ['phone' => '+1 (212) 555-0101', 'website_url' => 'https://corner-cafe.example'],
        ])->assertOk()->assertJsonPath('data.business.website_url', 'https://corner-cafe.example');

        $this->patchJson("/api/v1/_test/leads/{$lead->id}", [
            'qualification_status' => 'hot',
            'business' => [
                'website_url' => 'javascript:alert(1)',
                'phone' => 'call me maybe',
                'name' => '',
                'normalized_name' => 'sneaky',
            ],
        ])->assertStatus(422)->assertJsonValidationErrors([
            'qualification_status', 'business', 'business.website_url', 'business.phone', 'business.name',
        ]);
    }

    public function test_store_outreach_validates_input(): void
    {
        $lead = CampaignLead::factory()->for(Campaign::factory()->for($this->user))->create();

        $this->postJson("/api/v1/_test/leads/{$lead->id}/outreach", ['channel' => 'email', 'status' => 'contacted', 'contacted_at' => now()->subHour()->toIso8601String()])
            ->assertOk();

        $this->postJson("/api/v1/_test/leads/{$lead->id}/outreach", [
            'channel' => 'carrier_pigeon', 'status' => 'contacted', 'contacted_at' => now()->addDay()->toIso8601String(),
        ])->assertStatus(422)->assertJsonValidationErrors(['channel', 'contacted_at']);
    }

    public function test_store_outreach_blocks_new_contact_with_suppressed_business(): void
    {
        $lead = CampaignLead::factory()->for(Campaign::factory()->for($this->user))->create();
        SuppressionEntry::factory()->create(['email' => null, 'business_id' => $lead->business_id]);

        $this->postJson("/api/v1/_test/leads/{$lead->id}/outreach", ['channel' => 'email', 'status' => 'planned'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        // Logging an incoming reply is still allowed.
        $this->postJson("/api/v1/_test/leads/{$lead->id}/outreach", ['channel' => 'email', 'status' => 'not_interested'])
            ->assertOk();
    }
}

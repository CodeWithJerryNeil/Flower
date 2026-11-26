<?php

namespace App\Models;

use App\Enums\BusinessStatus;
use App\Enums\WebsiteStatus;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'normalized_name', 'category', 'address', 'city', 'state', 'country',
    'latitude', 'longitude', 'phone', 'website_url', 'website_status', 'business_status',
    'lead_score', 'last_researched_at',
])]
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    protected $attributes = [
        'website_status' => 'unknown',
        'business_status' => 'unknown',
    ];

    protected function casts(): array
    {
        return [
            'website_status' => WebsiteStatus::class,
            'business_status' => BusinessStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'lead_score' => 'integer',
            'last_researched_at' => 'datetime',
        ];
    }

    /** @return HasMany<BusinessProviderIdentity, $this> */
    public function providerIdentities(): HasMany
    {
        return $this->hasMany(BusinessProviderIdentity::class);
    }

    /** @return HasMany<Contact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /** @return HasMany<SocialProfile, $this> */
    public function socialProfiles(): HasMany
    {
        return $this->hasMany(SocialProfile::class);
    }

    /** @return HasMany<WebsiteCheck, $this> */
    public function websiteChecks(): HasMany
    {
        return $this->hasMany(WebsiteCheck::class);
    }

    /** @return HasMany<ProspectSource, $this> */
    public function prospectSources(): HasMany
    {
        return $this->hasMany(ProspectSource::class);
    }

    /** @return BelongsToMany<Campaign, $this, CampaignLead, 'lead'> */
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_leads')
            ->using(CampaignLead::class)
            ->as('lead')
            ->withPivot(['id', 'qualification_status', 'score', 'notes'])
            ->withTimestamps();
    }

    /** @return HasMany<CampaignLead, $this> */
    public function campaignLeads(): HasMany
    {
        return $this->hasMany(CampaignLead::class);
    }

    /** @return HasMany<OutreachActivity, $this> */
    public function outreachActivities(): HasMany
    {
        return $this->hasMany(OutreachActivity::class);
    }

    /** @return HasMany<SuppressionEntry, $this> */
    public function suppressionEntries(): HasMany
    {
        return $this->hasMany(SuppressionEntry::class);
    }

    public function isSuppressed(): bool
    {
        return $this->suppressionEntries()->exists();
    }

    /**
     * Businesses the user can reach through at least one of their own campaigns.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function accessibleBy(Builder $query, User $user): void
    {
        $query->whereHas('campaigns', fn (Builder $campaigns) => $campaigns->where('campaigns.user_id', $user->getKey()));
    }

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function withWebsiteStatus(Builder $query, WebsiteStatus ...$statuses): void
    {
        $query->whereIn('website_status', $statuses);
    }

    /**
     * Businesses confirmed to lack a dedicated website (social_only or no_website_found).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withoutDedicatedWebsite(Builder $query): void
    {
        $query->whereIn('website_status', WebsiteStatus::withoutDedicatedWebsite());
    }
}

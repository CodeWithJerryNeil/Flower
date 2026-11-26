<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * user_id and status are deliberately not mass assignable: ownership comes from the
 * authenticated user and status transitions belong to the orchestration layer.
 */
#[Fillable(['name', 'location_query', 'city', 'state', 'country', 'category', 'target_count', 'filters'])]
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
        'filters' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'filters' => 'array',
            'target_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Business, $this, CampaignLead, 'lead'> */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'campaign_leads')
            ->using(CampaignLead::class)
            ->as('lead')
            ->withPivot(['id', 'qualification_status', 'score', 'notes'])
            ->withTimestamps();
    }

    /** @return HasMany<CampaignLead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(CampaignLead::class);
    }

    /** @return HasMany<ResearchJob, $this> */
    public function researchJobs(): HasMany
    {
        return $this->hasMany(ResearchJob::class);
    }

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function ownedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function inProgress(Builder $query): void
    {
        $query->whereIn('status', [
            CampaignStatus::Discovering,
            CampaignStatus::Researching,
            CampaignStatus::Qualifying,
        ]);
    }
}

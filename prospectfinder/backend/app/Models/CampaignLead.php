<?php

namespace App\Models;

use App\Enums\QualificationStatus;
use Database\Factories\CampaignLeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/** A business's membership in a campaign, with its per-campaign qualification. */
#[Fillable(['campaign_id', 'business_id', 'qualification_status', 'score', 'notes'])]
class CampaignLead extends Pivot
{
    /** @use HasFactory<CampaignLeadFactory> */
    use HasFactory;

    protected $table = 'campaign_leads';

    public $incrementing = true;

    protected $attributes = [
        'qualification_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'qualification_status' => QualificationStatus::class,
            'score' => 'integer',
        ];
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

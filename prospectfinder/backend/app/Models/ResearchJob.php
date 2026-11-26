<?php

namespace App\Models;

use App\Enums\ResearchJobStatus;
use App\Enums\ResearchJobType;
use Database\Factories\ResearchJobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'campaign_id', 'business_id', 'job_type', 'status', 'attempts', 'error_message',
    'started_at', 'completed_at',
])]
class ResearchJob extends Model
{
    /** @use HasFactory<ResearchJobFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
        'attempts' => 0,
    ];

    protected function casts(): array
    {
        return [
            'job_type' => ResearchJobType::class,
            'status' => ResearchJobStatus::class,
            'attempts' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function terminal(Builder $query): void
    {
        $query->whereIn('status', array_filter(ResearchJobStatus::cases(), fn ($s) => $s->isTerminal()));
    }

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function unfinished(Builder $query): void
    {
        $query->whereIn('status', array_filter(ResearchJobStatus::cases(), fn ($s) => ! $s->isTerminal()));
    }
}

<?php

namespace App\Models;

use App\Enums\WebsiteStatus;
use Database\Factories\WebsiteCheckFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One row per detection run; history is never overwritten. */
#[Fillable([
    'business_id', 'checked_url', 'status', 'http_status', 'is_social_domain',
    'evidence', 'checked_at',
])]
class WebsiteCheck extends Model
{
    /** @use HasFactory<WebsiteCheckFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => WebsiteStatus::class,
            'http_status' => 'integer',
            'is_social_domain' => 'boolean',
            'evidence' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

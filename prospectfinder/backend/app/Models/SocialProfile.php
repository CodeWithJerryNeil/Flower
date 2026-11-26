<?php

namespace App\Models;

use App\Enums\SocialPlatform;
use Database\Factories\SocialProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'business_id', 'platform', 'profile_url', 'username', 'source_url',
    'confidence_score', 'discovered_at',
])]
class SocialProfile extends Model
{
    /** @use HasFactory<SocialProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'confidence_score' => 'integer',
            'discovered_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

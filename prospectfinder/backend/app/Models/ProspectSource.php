<?php

namespace App\Models;

use Database\Factories\ProspectSourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Provenance for a discovered field: where it came from and when. */
#[Fillable([
    'business_id', 'provider', 'source_type', 'source_url', 'field_name',
    'evidence', 'collected_at',
])]
class ProspectSource extends Model
{
    /** @use HasFactory<ProspectSourceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'collected_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

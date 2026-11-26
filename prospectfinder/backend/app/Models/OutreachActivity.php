<?php

namespace App\Models;

use App\Enums\OutreachChannel;
use App\Enums\OutreachStatus;
use Database\Factories\OutreachActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Manually tracked outreach. ProspectFinder does not send messages itself. */
#[Fillable(['business_id', 'channel', 'status', 'contacted_at', 'notes'])]
class OutreachActivity extends Model
{
    /** @use HasFactory<OutreachActivityFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'channel' => OutreachChannel::class,
            'status' => OutreachStatus::class,
            'contacted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

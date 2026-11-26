<?php

namespace App\Models;

use App\Support\EmailAddress;
use Database\Factories\SuppressionEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An opt-out or other do-not-contact record, by email address, by business, or both.
 * The database requires at least one of email or business_id.
 */
#[Fillable(['email', 'business_id', 'reason', 'source'])]
class SuppressionEntry extends Model
{
    /** @use HasFactory<SuppressionEntryFactory> */
    use HasFactory;

    protected $table = 'suppression_list';

    /** @return Attribute<string|null, string|null> */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => $value === null ? null : EmailAddress::normalize($value));
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public static function coversEmail(string $email): bool
    {
        return static::query()->where('email', EmailAddress::normalize($email))->exists();
    }
}

<?php

namespace App\Models;

use App\Enums\ContactType;
use App\Enums\VerificationStatus;
use App\Support\EmailAddress;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * A publicly discovered contact. Never create one from a guessed address.
 */
#[Fillable([
    'business_id', 'name', 'email', 'role', 'contact_type', 'verification_status',
    'confidence_score', 'source_url', 'discovered_at',
])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected $attributes = [
        'verification_status' => 'unverified',
    ];

    protected function casts(): array
    {
        return [
            'contact_type' => ContactType::class,
            'verification_status' => VerificationStatus::class,
            'confidence_score' => 'integer',
            'discovered_at' => 'datetime',
        ];
    }

    /** @return Attribute<string, string> */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value) => EmailAddress::normalize($value));
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** Suppressed by its own email address or by its business. */
    public function isSuppressed(): bool
    {
        return SuppressionEntry::query()
            ->where('email', $this->email)
            ->orWhere('business_id', $this->business_id)
            ->exists();
    }

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function suppressed(Builder $query): void
    {
        $query->whereExists($this->suppressionMatch(...));
    }

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function notSuppressed(Builder $query): void
    {
        $query->whereNotExists($this->suppressionMatch(...));
    }

    private function suppressionMatch(QueryBuilder $sub): void
    {
        $sub->selectRaw('1')
            ->from('suppression_list')
            ->where(fn (QueryBuilder $q) => $q
                ->whereColumn('suppression_list.email', 'contacts.email')
                ->orWhereColumn('suppression_list.business_id', 'contacts.business_id'));
    }
}

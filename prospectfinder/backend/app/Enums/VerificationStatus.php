<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Unverified = 'unverified';
    case SyntaxValid = 'syntax_valid';
    case DomainVerified = 'domain_verified';
    case DeliverabilityVerified = 'deliverability_verified';
    case Invalid = 'invalid';
    case Unknown = 'unknown';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

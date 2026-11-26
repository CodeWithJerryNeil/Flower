<?php

namespace App\Enums;

enum QualificationStatus: string
{
    case Pending = 'pending';
    case Qualified = 'qualified';
    case NeedsReview = 'needs_review';
    case Rejected = 'rejected';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

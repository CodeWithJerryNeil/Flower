<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Pending = 'pending';
    case Discovering = 'discovering';
    case Researching = 'researching';
    case Qualifying = 'qualifying';
    case Completed = 'completed';
    case PartiallyCompleted = 'partially_completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Whether no further processing will happen in this state. */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::PartiallyCompleted, self::Failed, self::Cancelled], true);
    }
}

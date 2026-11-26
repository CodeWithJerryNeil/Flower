<?php

namespace App\Enums;

enum OutreachStatus: string
{
    case Planned = 'planned';
    case Contacted = 'contacted';
    case Replied = 'replied';
    case Interested = 'interested';
    case NotInterested = 'not_interested';
    case Won = 'won';
    case Lost = 'lost';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

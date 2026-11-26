<?php

namespace App\Enums;

enum OutreachChannel: string
{
    case Email = 'email';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case Phone = 'phone';
    case Other = 'other';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum WebsiteStatus: string
{
    case Unknown = 'unknown';
    case HasWebsite = 'has_website';
    case SocialOnly = 'social_only';
    case NoWebsiteFound = 'no_website_found';
    case Inaccessible = 'inaccessible';
    case Uncertain = 'uncertain';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Statuses that represent a confirmed lack of a dedicated website (prospect opportunities). */
    public static function withoutDedicatedWebsite(): array
    {
        return [self::SocialOnly, self::NoWebsiteFound];
    }
}

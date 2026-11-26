<?php

namespace App\Enums;

enum ResearchJobType: string
{
    case Discovery = 'discovery';
    case WebsiteCheck = 'website_check';
    case ContactResearch = 'contact_research';
    case Qualification = 'qualification';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

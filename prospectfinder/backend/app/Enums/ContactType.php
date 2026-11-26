<?php

namespace App\Enums;

enum ContactType: string
{
    case BusinessEmail = 'business_email';
    case OwnerEmail = 'owner_email';
    case ManagerEmail = 'manager_email';
    case GeneralContact = 'general_contact';
    case Other = 'other';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

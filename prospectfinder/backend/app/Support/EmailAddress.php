<?php

namespace App\Support;

final class EmailAddress
{
    /**
     * Canonical form used for storage, deduplication and suppression matching.
     * Lowercases the whole address: local parts are case-insensitive in practice.
     */
    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email));
    }
}

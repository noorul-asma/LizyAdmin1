<?php

namespace App\Enums;

enum ListingType: string
{
    case Sale = 'sale';
    case Rent = 'rent';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}

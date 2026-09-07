<?php

namespace App\Enums;

enum PricingType: string
{
    case Fixed = 'fixed';
    case Hourly = 'hourly';
    case Quote = 'quote';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}

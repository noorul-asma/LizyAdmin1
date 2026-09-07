<?php

namespace App\Enums;

/**
 * Universal publishing lifecycle shared by every listing module
 * (Products, Properties, Tour Packages, Services, ...).
 */
enum PublishingStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Scheduled = 'scheduled';
    case Expired = 'expired';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::Published => 'Published',
            self::Scheduled => 'Scheduled',
            self::Expired => 'Expired',
            self::Archived => 'Archived',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}

<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Gives any model a polymorphic media library (main image, gallery, documents, videos)
 * without duplicating image/gallery columns on every module's table.
 */
trait HasMediaItems
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function mainImage(): ?Media
    {
        return $this->media->firstWhere('collection', 'main')
            ?? $this->media->firstWhere('type', 'image');
    }

    /** Looks up a single image by its exact collection name - used for named, purpose-specific image slots (e.g. TourPackage's Circle/Banner/Trending Destination/Best Sellers/View Details images). */
    public function mediaByCollection(string $collection): ?Media
    {
        return $this->media->firstWhere('collection', $collection);
    }

    public function gallery()
    {
        return $this->media->where('collection', 'gallery');
    }
}

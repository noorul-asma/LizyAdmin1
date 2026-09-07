<?php

namespace App\Models\Concerns;

use App\Enums\PublishingStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Universal draft/publish lifecycle for any listing module.
 * Handles the status enum cast, published_at stamping and shared query scopes.
 */
trait HasPublishing
{
    public static function bootHasPublishing(): void
    {
        static::saving(function ($model) {
            if ($model->status === PublishingStatus::Published->value && empty($model->published_at)) {
                $model->published_at = now();
            }
        });
    }

    /** Records visible on the public website: published, and not expired/scheduled ahead. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublishingStatus::Published->value)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function isPublished(): bool
    {
        return $this->status === PublishingStatus::Published->value;
    }
}

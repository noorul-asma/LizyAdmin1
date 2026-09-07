<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Auto-generates a unique slug from a source field (name/title) whenever
 * a record is created, or when that field changes on update.
 *
 * A model using this trait may optionally define:
 *   protected string $slugSourceField = 'title';   // default: 'name'
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        // NOTE: there used to be an `updating` hook here that regenerated
        // the slug whenever the name field changed. That's what broke
        // "edit isn't syncing to LizyNet": the admin edit form only ever
        // sends `name` (there's no slug field in the UI), so isDirty('slug')
        // was always false and every single name edit silently rewrote the
        // slug - turning "service.html?slug=old-slug", already rendered on
        // any page the moment before, into a dead 404 link. Slugs are now
        // stable once created; editing a record's name no longer changes
        // its URL.
    }

    public function generateUniqueSlug(): string
    {
        $source = $this->slugSourceField ?? 'name';
        $base = Str::slug($this->{$source} ?: Str::random(8));
        $slug = $base;
        $i = 1;

        $query = $this->newSlugUniquenessQuery()->where('slug', $slug);
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        while ($query->exists()) {
            $slug = $base.'-'.(++$i);
            $query = $this->newSlugUniquenessQuery()->where('slug', $slug);
            if ($this->exists) {
                $query->where('id', '!=', $this->id);
            }
        }

        return $slug;
    }

    /**
     * Base query for the uniqueness check above. `withTrashed()` only
     * exists on models using the SoftDeletes trait (Product, Property,
     * TourPackage, Service) - calling it unconditionally broke every model
     * that uses HasSlug without SoftDeletes (Category, Location) with
     * "Call to undefined method withTrashed()". It only failed
     * intermittently rather than every time because Eloquent registers
     * withTrashed()/onlyTrashed() as global macros on the shared
     * Illuminate\Database\Eloquent\Builder class the first time any
     * SoftDeletes model boots - so the exact error depended on whether some
     * unrelated model had already booted earlier in that same request.
     *
     * Detected with class_uses_recursive() rather than method_exists() -
     * withTrashed() is never a real declared method (Model forwards it to
     * the query builder via __call, and the builder itself only has it as
     * a macro), so method_exists() reports false for every model, soft
     * deletes or not.
     */
    protected function newSlugUniquenessQuery()
    {
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(static::class)
        );

        return $usesSoftDeletes ? static::withTrashed() : static::query();
    }
}

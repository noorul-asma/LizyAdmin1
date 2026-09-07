<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasSlug;

    protected $fillable = ['parent_id', 'type', 'name', 'slug', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /** Full "Area, City, State, Country" style breadcrumb. */
    public function breadcrumb(): string
    {
        $names = [$this->name];
        $node = $this;
        while ($node->parent) {
            $node = $node->parent;
            $names[] = $node->name;
        }

        return implode(', ', $names);
    }
}

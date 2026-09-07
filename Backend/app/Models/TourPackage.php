<?php

namespace App\Models;

use App\Models\Concerns\HasEnquiries;
use App\Models\Concerns\HasMediaItems;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourPackage extends Model
{
    use HasSlug, HasPublishing, HasMediaItems, HasEnquiries, SoftDeletes;

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'category_id', 'destination_id', 'created_by',
        'name', 'subtitle', 'type', 'tags', 'badge', 'slug', 'destination_name',
        'duration_days', 'duration_nights', 'price', 'original_price', 'discount',
        'description', 'includes', 'itinerary', 'inclusions', 'exclusions', 'hotels', 'activities',
        'camp_pricing', 'terms_conditions', 'status', 'is_featured', 'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'badge' => 'array',
        'itinerary' => 'array',
        'inclusions' => 'array',
        'exclusions' => 'array',
        'hotels' => 'array',
        'activities' => 'array',
        'camp_pricing' => 'array',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function durationLabel(): string
    {
        return "{$this->duration_days}D/{$this->duration_nights}N";
    }
}

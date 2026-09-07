<?php

namespace App\Models;

use App\Models\Concerns\HasEnquiries;
use App\Models\Concerns\HasMediaItems;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasSlug, HasPublishing, HasMediaItems, HasEnquiries, SoftDeletes;

    protected string $slugSourceField = 'title';

    protected $fillable = [
        'category_id', 'location_id', 'created_by',
        'title', 'slug', 'listing_type', 'possession_status', 'area_name', 'price', 'price_unit', 'price_max',
        'bedrooms', 'bathrooms', 'built_up_area', 'plot_area', 'plot_area_min', 'plot_area_max', 'plot_area_unit', 'facing', 'furnishing_status',
        'approval_status', 'bank_loan_available', 'bank_loan_percentage', 'registration_status', 'booking_advance',
        'nearby_landmarks', 'special_offer',
        'amenities', 'description', 'status', 'is_featured', 'is_verified', 'rera_number', 'published_at',
    ];

    protected $casts = [
        'amenities' => 'array',
        'price' => 'decimal:2',
        'price_max' => 'decimal:2',
        'plot_area_min' => 'decimal:2',
        'plot_area_max' => 'decimal:2',
        'bank_loan_available' => 'boolean',
        'bank_loan_percentage' => 'decimal:2',
        'built_up_area' => 'decimal:2',
        'plot_area' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class); // property type: Villa, Apartment, Plot ...
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

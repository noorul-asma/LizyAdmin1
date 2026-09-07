<?php

namespace App\Models;

use App\Models\Concerns\HasEnquiries;
use App\Models\Concerns\HasMediaItems;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasSlug, HasPublishing, HasMediaItems, HasEnquiries, SoftDeletes;

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'category_id', 'subcategory_id', 'location_id', 'created_by',
        'name', 'slug', 'brand', 'sku', 'price', 'sale_price',
        'description', 'specifications', 'status', 'is_featured', 'published_at',
    ];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function displayPrice(): string
    {
        return (string) ($this->sale_price ?? $this->price);
    }
}

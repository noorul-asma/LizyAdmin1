<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Enquiry extends Model
{
    protected $fillable = [
        'enquiryable_type', 'enquiryable_id', 'name', 'email', 'phone',
        'message', 'cta_type', 'status', 'source',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function enquiryable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }
}

<?php

namespace App\Models\Concerns;

use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasEnquiries
{
    public function enquiries(): MorphMany
    {
        return $this->morphMany(Enquiry::class, 'enquiryable');
    }
}

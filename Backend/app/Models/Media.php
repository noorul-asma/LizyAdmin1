<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    // Friendly module key (as used by the Media Library admin page's Module
    // dropdown and by Category::MODULES) mapped to the Eloquent class the
    // polymorphic mediable_type column actually stores. Central lookup so
    // MediaController and MediaResource never hardcode this twice.
    public const MODULE_MAP = [
        'product' => \App\Models\Product::class,
        'property' => \App\Models\Property::class,
        'tour_package' => \App\Models\TourPackage::class,
        'service' => \App\Models\Service::class,
    ];

    protected $fillable = [
        'mediable_type', 'mediable_id', 'type', 'collection', 'disk',
        'path', 'original_name', 'mime_type', 'size', 'alt_text', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'size' => 'integer',
    ];

    protected $appends = ['url'];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        // MediaStreamController (routes/web.php: GET /files/{path}) serves
        // files straight from disk without needing the public/storage
        // symlink - see that controller's docblock for why.
        //
        // Route prefix is "/files/", not "/media/" - the stored path
        // itself already starts with "media/{year}/{month}/" (see
        // MediaController::store()), so a "/media/{path}" route produced
        // "/media/" + "media/2026/08/xyz.jpg" = a doubled "/media/media/..."
        // segment that failed to resolve. "/files/" has no overlap with
        // the storage subfolder name, so this can never collide again.
        //
        // Built with url() rather than route('media.show', [...]) on
        // purpose: Laravel's route() helper rawurlencodes '/' to '%2F'
        // inside parameter values, which would mangle a multi-segment
        // path like "media/2026/08/xyz.jpg". url() just concatenates, so
        // the path's own slashes survive exactly as stored.
        return url('/files/'.$this->path);
    }
}

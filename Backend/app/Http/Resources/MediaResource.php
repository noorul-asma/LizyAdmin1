<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'collection' => $this->collection,
            'url' => $this->url,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
            'mediable_type' => $this->mediable_type ? class_basename($this->mediable_type) : null,
            'mediable_id' => $this->mediable_id,
            // Friendly module key (product/property/tour_package/service) matching
            // Media::MODULE_MAP - what the Media Library page's Module column/filter
            // and Add/Edit form actually key off, instead of the raw class name above.
            'module' => $this->mediable_type ? (array_flip(Media::MODULE_MAP)[$this->mediable_type] ?? null) : null,
            'mediable_name' => $this->whenLoaded('mediable', fn () => $this->mediable?->name ?? $this->mediable?->title ?? null),
            'created_at' => $this->created_at,
        ];
    }
}

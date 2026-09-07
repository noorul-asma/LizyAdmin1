<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'provider_name' => $this->provider_name,
            'pricing_type' => $this->pricing_type,
            'price' => $this->price,
            'description' => $this->description,
            'intro' => $this->intro,
            'features' => $this->features,
            'outcomes' => $this->outcomes,
            'process' => $this->process,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'main_image' => $this->whenLoaded('media', fn () => $this->mainImage()?->url),
            'gallery' => $this->whenLoaded('media', fn () => MediaResource::collection($this->gallery())),
            'available_ctas' => config('portal.ctas.service', []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

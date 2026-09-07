<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'module' => $this->module,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            // Banner images for this category (e.g. Land/Apartments/Houses
            // on LizyRealty) - real admin-uploaded photos, only present
            // when 'media' was eager-loaded by the calling controller.
            'banners' => $this->whenLoaded('media', fn () => MediaResource::collection($this->gallery())->values()),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}

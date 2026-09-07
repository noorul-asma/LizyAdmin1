<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'breadcrumb' => $this->breadcrumb(),
            'children' => LocationResource::collection($this->whenLoaded('children')),
        ];
    }
}

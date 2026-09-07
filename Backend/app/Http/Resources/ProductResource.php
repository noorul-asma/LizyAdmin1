<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brand,
            'sku' => $this->sku,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'description' => $this->description,
            'specifications' => $this->specifications,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'subcategory' => new CategoryResource($this->whenLoaded('subcategory')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'main_image' => $this->whenLoaded('media', fn () => $this->mainImage()?->url),
            'gallery' => $this->whenLoaded('media', fn () => MediaResource::collection($this->gallery())),
            'available_ctas' => config('portal.ctas.product', []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

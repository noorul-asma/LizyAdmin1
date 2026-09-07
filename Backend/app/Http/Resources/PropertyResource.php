<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'listing_type' => $this->listing_type,
            'possession_status' => $this->possession_status,
            'approval_status' => $this->approval_status,
            'bank_loan_available' => $this->bank_loan_available,
            'bank_loan_percentage' => $this->bank_loan_percentage,
            'registration_status' => $this->registration_status,
            'booking_advance' => $this->booking_advance,
            'nearby_landmarks' => $this->nearby_landmarks,
            'special_offer' => $this->special_offer,
            'area_name' => $this->area_name,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'price_max' => $this->price_max,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'built_up_area' => $this->built_up_area,
            'plot_area' => $this->plot_area,
            'plot_area_min' => $this->plot_area_min,
            'plot_area_max' => $this->plot_area_max,
            'plot_area_unit' => $this->plot_area_unit,
            'facing' => $this->facing,
            'furnishing_status' => $this->furnishing_status,
            'amenities' => $this->amenities,
            'description' => $this->description,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_verified' => $this->is_verified,
            'rera_number' => $this->rera_number,
            'published_at' => $this->published_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'main_image' => $this->whenLoaded('media', fn () => $this->mainImage()?->url),
            'banner_image' => $this->whenLoaded('media', fn () => $this->mediaByCollection('banner')?->url),
            'gallery' => $this->whenLoaded('media', fn () => MediaResource::collection($this->gallery())),
            'available_ctas' => config('portal.ctas.property', []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

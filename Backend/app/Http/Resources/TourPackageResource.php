<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subtitle' => $this->subtitle,
            'type' => $this->type,
            'tags' => $this->tags,
            'badge' => $this->badge,
            'slug' => $this->slug,
            'destination_name' => $this->destination_name,
            'duration_days' => $this->duration_days,
            'duration_nights' => $this->duration_nights,
            'duration_label' => $this->durationLabel(),
            'price' => $this->price,
            'original_price' => $this->original_price,
            'discount' => $this->discount,
            'description' => $this->description,
            'includes' => $this->includes,
            'itinerary' => $this->itinerary,
            'inclusions' => $this->inclusions,
            'exclusions' => $this->exclusions,
            'hotels' => $this->hotels,
            'activities' => $this->activities,
            'camp_pricing' => $this->camp_pricing,
            'terms_conditions' => $this->terms_conditions,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'destination' => new LocationResource($this->whenLoaded('destination')),
            'main_image' => $this->whenLoaded('media', fn () => $this->mainImage()?->url),
            'gallery' => $this->whenLoaded('media', fn () => MediaResource::collection($this->gallery())),
            // Named, purpose-specific image slots - each independently
            // managed in Lizy Admin and independently consumed on LizyGo,
            // never mixed with each other or with main_image/gallery.
            'circle_image' => $this->whenLoaded('media', fn () => $this->mediaByCollection('circle')?->url),
            'banner_image' => $this->whenLoaded('media', fn () => $this->mediaByCollection('banner')?->url),
            'trending_destination_image' => $this->whenLoaded('media', fn () => $this->mediaByCollection('trending_destination')?->url),
            'best_sellers_image' => $this->whenLoaded('media', fn () => $this->mediaByCollection('best_sellers')?->url),
            'view_details_image_1' => $this->whenLoaded('media', fn () => $this->mediaByCollection('view_details_1')?->url),
            'view_details_image_2' => $this->whenLoaded('media', fn () => $this->mediaByCollection('view_details_2')?->url),
            'view_details_image_3' => $this->whenLoaded('media', fn () => $this->mediaByCollection('view_details_3')?->url),
            'available_ctas' => config('portal.ctas.tour_package', []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\AbstractAdminListingController;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends AbstractAdminListingController
{
    protected string $modelClass = Property::class;

    protected string $resourceClass = PropertyResource::class;

    protected string $moduleKey = 'property';

    protected array $with = ['category', 'location', 'media'];

    protected array $searchable = ['title', 'area_name'];

    protected array $filterable = ['category_id', 'location_id', 'listing_type', 'status', 'is_featured', 'is_verified'];

    protected array $csvColumns = ['id', 'category_id', 'title', 'listing_type', 'price', 'price_unit', 'price_max', 'plot_area_min', 'plot_area_max', 'plot_area_unit', 'approval_status', 'bank_loan_available', 'bank_loan_percentage', 'registration_status', 'booking_advance', 'nearby_landmarks', 'special_offer', 'bedrooms', 'bathrooms', 'status'];

    protected function rules(Request $request, ?int $id = null): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:properties,slug'.($id ? ",{$id}" : '')],
            'listing_type' => ['required', 'string', 'in:sale,rent'],
            'possession_status' => ['nullable', 'string', 'in:ready_to_move,under_construction'],
            'approval_status' => ['nullable', 'string', 'max:80'],
            'bank_loan_available' => ['nullable', 'boolean'],
            'bank_loan_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'registration_status' => ['nullable', 'string', 'max:80'],
            'booking_advance' => ['nullable', 'string', 'max:80'],
            'nearby_landmarks' => ['nullable', 'string'],
            'special_offer' => ['nullable', 'string'],
            'area_name' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_unit' => ['nullable', 'string', 'max:40'],
            'price_max' => ['nullable', 'numeric', 'min:0', 'gte:price'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'built_up_area' => ['nullable', 'numeric', 'min:0'],
            'plot_area' => ['nullable', 'numeric', 'min:0'],
            'plot_area_min' => ['nullable', 'numeric', 'min:0'],
            'plot_area_max' => ['nullable', 'numeric', 'min:0', 'gte:plot_area_min'],
            'plot_area_unit' => ['nullable', 'string', 'max:30'],
            'facing' => ['nullable', 'string', 'in:north,south,east,west,north_east,north_west,south_east,south_west'],
            'furnishing_status' => ['nullable', 'string', 'in:unfurnished,semi_furnished,fully_furnished'],
            'amenities' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,pending_review,published,scheduled,expired,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'is_verified' => ['nullable', 'boolean'],
            'rera_number' => ['nullable', 'string', 'max:60'],
        ];
    }
}

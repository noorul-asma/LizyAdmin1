<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\AbstractAdminListingController;
use App\Http\Resources\TourPackageResource;
use App\Models\TourPackage;
use Illuminate\Http\Request;

class TourPackageController extends AbstractAdminListingController
{
    protected string $modelClass = TourPackage::class;

    protected string $resourceClass = TourPackageResource::class;

    protected string $moduleKey = 'tour_package';

    protected array $with = ['category', 'destination', 'media'];

    protected array $searchable = ['name', 'destination_name'];

    protected array $filterable = ['category_id', 'destination_id', 'status', 'is_featured'];

    protected array $csvColumns = ['id', 'category_id', 'name', 'type', 'destination_name', 'duration_days', 'duration_nights', 'price', 'original_price', 'status'];

    protected function rules(Request $request, ?int $id = null): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'destination_id' => ['nullable', 'integer', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'badge' => ['nullable', 'array'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tour_packages,slug'.($id ? ",{$id}" : '')],
            'destination_name' => ['nullable', 'string', 'max:255'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'duration_nights' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'itinerary' => ['nullable', 'array'],
            'itinerary.*' => ['array'],
            'camp_pricing' => ['nullable', 'array'],
            'inclusions' => ['nullable', 'array'],
            'exclusions' => ['nullable', 'array'],
            'hotels' => ['nullable', 'array'],
            'activities' => ['nullable', 'array'],
            'terms_conditions' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,pending_review,published,scheduled,expired,archived'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}

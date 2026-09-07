<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\Public\Concerns\AbstractPublicListingController;
use App\Http\Resources\PropertyResource;
use App\Models\Property;

class PropertyController extends AbstractPublicListingController
{
    protected string $modelClass = Property::class;

    protected string $resourceClass = PropertyResource::class;

    protected array $with = ['category', 'location', 'media'];

    protected array $searchable = ['title', 'area_name'];

    protected array $filterable = ['category_id', 'listing_type'];
}

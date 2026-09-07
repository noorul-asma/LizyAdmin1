<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\Public\Concerns\AbstractPublicListingController;
use App\Http\Resources\ServiceResource;
use App\Models\Service;

class ServiceController extends AbstractPublicListingController
{
    protected string $modelClass = Service::class;

    protected string $resourceClass = ServiceResource::class;

    protected array $with = ['category', 'location', 'media'];

    protected array $searchable = ['name', 'provider_name'];

    protected array $filterable = ['category_id', 'pricing_type'];
}

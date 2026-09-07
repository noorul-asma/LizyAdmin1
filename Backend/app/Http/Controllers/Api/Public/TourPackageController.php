<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\Public\Concerns\AbstractPublicListingController;
use App\Http\Resources\TourPackageResource;
use App\Models\TourPackage;

class TourPackageController extends AbstractPublicListingController
{
    protected string $modelClass = TourPackage::class;

    protected string $resourceClass = TourPackageResource::class;

    protected array $with = ['category', 'destination', 'media'];

    protected array $searchable = ['name', 'destination_name'];

    protected array $filterable = ['category_id'];

    protected string $locationColumn = 'destination_id';

    protected string $locationRelation = 'destination';
}

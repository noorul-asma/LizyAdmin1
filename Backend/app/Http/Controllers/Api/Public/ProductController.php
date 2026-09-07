<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\Public\Concerns\AbstractPublicListingController;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends AbstractPublicListingController
{
    protected string $modelClass = Product::class;

    protected string $resourceClass = ProductResource::class;

    protected array $with = ['category', 'subcategory', 'location', 'media'];

    protected array $searchable = ['name', 'brand', 'sku'];

    protected array $filterable = ['category_id', 'subcategory_id', 'brand'];
}

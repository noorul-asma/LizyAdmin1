<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\AbstractAdminListingController;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends AbstractAdminListingController
{
    protected string $modelClass = Product::class;

    protected string $resourceClass = ProductResource::class;

    protected string $moduleKey = 'product';

    protected array $with = ['category', 'subcategory', 'location', 'media'];

    protected array $searchable = ['name', 'brand', 'sku'];

    protected array $filterable = ['category_id', 'subcategory_id', 'location_id', 'status', 'is_featured', 'brand'];

    protected array $csvColumns = ['id', 'category_id', 'name', 'brand', 'sku', 'price', 'sale_price', 'status'];

    protected function rules(Request $request, ?int $id = null): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:categories,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'.($id ? ",{$id}" : '')],
            'brand' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'.($id ? ",{$id}" : '')],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:draft,pending_review,published,scheduled,expired,archived'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\Concerns\AbstractAdminListingController;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends AbstractAdminListingController
{
    protected string $modelClass = Service::class;

    protected string $resourceClass = ServiceResource::class;

    protected string $moduleKey = 'service';

    protected array $with = ['category', 'location', 'media'];

    protected array $searchable = ['name', 'provider_name'];

    protected array $filterable = ['category_id', 'location_id', 'pricing_type', 'status', 'is_featured'];

    protected array $csvColumns = ['id', 'category_id', 'name', 'provider_name', 'pricing_type', 'price', 'status'];

    protected function rules(Request $request, ?int $id = null): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:services,slug'.($id ? ",{$id}" : '')],
            'provider_name' => ['nullable', 'string', 'max:255'],
            'pricing_type' => ['required', 'string', 'in:fixed,hourly,quote'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'intro' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'outcomes' => ['nullable', 'array'],
            'process' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:draft,pending_review,published,scheduled,expired,archived'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}

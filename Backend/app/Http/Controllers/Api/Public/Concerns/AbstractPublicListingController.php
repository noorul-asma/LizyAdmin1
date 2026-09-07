<?php

namespace App\Http\Controllers\Api\Public\Concerns;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Universal Public Listing Engine - the read-only counterpart of
 * AbstractAdminListingController. Powers the "Discovery / Category /
 * Listing / Detail" layers from the Universal Portal Architecture doc for
 * every module, always scoped to published records only.
 */
abstract class AbstractPublicListingController extends Controller
{
    protected string $modelClass;

    protected string $resourceClass;

    protected array $with = ['category', 'location', 'media'];

    protected array $searchable = ['name'];

    /** Simple exact-match filters, e.g. category_id, listing_type. */
    protected array $filterable = ['category_id'];

    /** DB column + relation used for location filtering - TourPackage overrides these to "destination_id" / "destination". */
    protected string $locationColumn = 'location_id';

    protected string $locationRelation = 'location';

    public function index(Request $request)
    {
        $query = $this->modelClass::query()->with($this->with)->published();

        $this->applySearch($query, $request);
        $this->applyFilters($query, $request);
        $this->applyLocationFilter($query, $request);
        $this->applyPriceRange($query, $request);

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $sortBy = in_array($request->get('sort_by'), ['price', 'created_at'], true) ? $request->get('sort_by') : 'created_at';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = min((int) $request->get('per_page', config('portal.per_page', 15)), config('portal.max_per_page', 100));

        return $this->resourceClass::collection($query->paginate($perPage));
    }

    public function show(string $slug)
    {
        $model = $this->modelClass::with($this->with)->published()->where('slug', $slug)->firstOrFail();

        return new $this->resourceClass($model);
    }

    protected function applySearch(Builder $query, Request $request): void
    {
        $q = $request->get('q');
        if (! $q || empty($this->searchable)) {
            return;
        }

        $query->where(function (Builder $sub) use ($q) {
            foreach ($this->searchable as $column) {
                $sub->orWhere($column, 'like', "%{$q}%");
            }
        });
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        foreach ($this->filterable as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->get($field));
            }
        }
    }

    /** Accepts either location_id, or a location "slug" resolved against the locations table. */
    protected function applyLocationFilter(Builder $query, Request $request): void
    {
        if ($request->filled('location_id')) {
            $query->where($this->locationColumn, $request->get('location_id'));

            return;
        }

        if ($request->filled('location')) {
            $query->whereHas($this->locationRelation, fn (Builder $q) => $q->where('slug', $request->get('location')));
        }
    }

    protected function applyPriceRange(Builder $query, Request $request): void
    {
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->get('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->get('price_max'));
        }
    }
}

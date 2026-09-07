<?php

namespace App\Http\Controllers\Api\Admin\Concerns;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Universal Admin Listing Engine.
 *
 * Every business module (Products, Properties, Tour Packages, Services, and
 * anything added later) gets full CRUD + search + filters + bulk ops +
 * CSV import/export for free by extending this class and describing itself
 * through a handful of properties/methods below. This is what lets a new
 * module be added "without rebuilding the core" per the architecture spec.
 */
abstract class AbstractAdminListingController extends Controller
{
    /** Fully-qualified Eloquent model class, e.g. \App\Models\Product::class */
    protected string $modelClass;

    /** JsonResource class used to shape API responses. */
    protected string $resourceClass;

    /** Module key used for CTA config / permission slugs, e.g. "product". */
    protected string $moduleKey;

    /** Relations eager-loaded for index/show. */
    protected array $with = ['category', 'location', 'media'];

    /** Columns matched by the free-text `q` search parameter. */
    protected array $searchable = ['name'];

    /** Columns that can be filtered with an exact-match query param of the same name. */
    protected array $filterable = ['category_id', 'location_id', 'status', 'is_featured'];

    /** Columns exported to / imported from CSV. */
    protected array $csvColumns = ['id', 'name', 'price', 'status'];

    /** Validation rules. $id is present on update so unique rules can ignore the current row. */
    abstract protected function rules(Request $request, ?int $id = null): array;

    public function index(Request $request)
    {
        $query = $this->modelClass::query()->with($this->with);

        $this->applySearch($query, $request);
        $this->applyFilters($query, $request);
        $this->applySort($query, $request);

        $perPage = min((int) $request->get('per_page', config('portal.per_page', 15)), config('portal.max_per_page', 100));

        return $this->resourceClass::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = Validator::make($request->all(), $this->rules($request))->validate();
        $data['created_by'] = $request->user()?->id;
        // `?:` (not `??`) deliberately — an empty string from a form select
        // left on its blank placeholder option is just as "unset" as null
        // here, and should still fall back to draft rather than being
        // saved as a literal blank status that matches no filter anywhere.
        $data['status'] = $data['status'] ?: 'draft';

        $model = DB::transaction(function () use ($data, $request) {
            $model = $this->modelClass::create($data);
            $this->syncMedia($model, $request->input('media_ids', []));

            return $model;
        });

        return new $this->resourceClass($model->load($this->with));
    }

    public function show(int $id)
    {
        $model = $this->modelClass::with($this->with)->findOrFail($id);

        return new $this->resourceClass($model);
    }

    public function update(Request $request, int $id)
    {
        $model = $this->modelClass::findOrFail($id);
        $data = Validator::make($request->all(), $this->rules($request, $id))->validate();

        DB::transaction(function () use ($model, $data, $request) {
            $model->update($data);
            if ($request->has('media_ids')) {
                $this->syncMedia($model, $request->input('media_ids', []));
            }
        });

        return new $this->resourceClass($model->fresh($this->with));
    }

    public function destroy(int $id)
    {
        $model = $this->modelClass::findOrFail($id);
        $model->delete();

        return response()->json(['message' => 'Deleted (moved to trash).']);
    }

    public function restore(int $id)
    {
        $model = $this->modelClass::withTrashed()->findOrFail($id);
        $model->restore();

        return new $this->resourceClass($model->load($this->with));
    }

    public function forceDelete(int $id)
    {
        $model = $this->modelClass::withTrashed()->findOrFail($id);
        $model->media()->delete();
        $model->forceDelete();

        return response()->json(['message' => 'Permanently deleted.']);
    }

    // ---------------------------------------------------------------
    // Bulk operations
    // ---------------------------------------------------------------

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'status' => ['required', 'string', 'in:draft,pending_review,published,scheduled,expired,archived'],
        ]);

        $count = $this->modelClass::whereIn('id', $data['ids'])->update([
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published' ? now() : null,
        ]);

        return response()->json(['message' => "{$count} record(s) updated.", 'status' => $data['status']]);
    }

    public function bulkCategory(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $count = $this->modelClass::whereIn('id', $data['ids'])->update(['category_id' => $data['category_id']]);

        return response()->json(['message' => "{$count} record(s) moved."]);
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $count = $this->modelClass::whereIn('id', $data['ids'])->delete();

        return response()->json(['message' => "{$count} record(s) deleted."]);
    }

    // ---------------------------------------------------------------
    // CSV import / export
    // ---------------------------------------------------------------

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->modelClass::query();
        $this->applySearch($query, $request);
        $this->applyFilters($query, $request);
        $columns = $this->csvColumns;

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            $query->chunk(200, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(fn ($c) => data_get($row, $c), $columns));
                }
            });
            fclose($out);
        }, "{$this->moduleKey}-export-".now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $created = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            $payload = array_combine($header, $row) ?: [];
            $payload = array_intersect_key($payload, array_flip($this->csvColumns));
            unset($payload['id']);
            $payload['status'] = $payload['status'] ?? 'draft';
            $payload['created_by'] = $request->user()?->id;

            $validator = Validator::make($payload, $this->rules($request));
            if ($validator->fails()) {
                $errors[] = "Row {$line}: ".$validator->errors()->first();

                continue;
            }

            $this->modelClass::create($validator->validated());
            $created++;
        }
        fclose($handle);

        return response()->json(['created' => $created, 'errors' => $errors]);
    }

    // ---------------------------------------------------------------
    // Query helpers
    // ---------------------------------------------------------------

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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }
    }

    protected function applySort(Builder $query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowed = array_merge($this->filterable, $this->searchable, ['created_at', 'price', 'id']);

        if (in_array($sortBy, array_unique($allowed), true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /** Attach previously-uploaded, unattached Media rows to this record. */
    protected function syncMedia($model, array $mediaIds): void
    {
        if (empty($mediaIds)) {
            return;
        }

        // Single-image slots: uploading a replacement should always genuinely
        // replace the old one, not add a second row pointing at the same
        // record (which broke "replace the photo" edits before - see below).
        // 'main' is the original case; the others are TourPackage's
        // purpose-specific image slots (Circle/Banner/Trending Destination/
        // Best Sellers/View Details 1-3) - each is independently
        // replaceable without touching any of the others.
        //
        // Only collections actually present among the newly-attached media
        // ids get their old entry detached (looked up dynamically, not
        // assumed) - so saving a new Circle Image never touches Package
        // Image, Banner Image, or anything else that wasn't part of this
        // save. 'gallery' is deliberately never included here: it's
        // additive/multi-image by design (see the 8-slot Package Gallery
        // admin UI), so it must never be auto-detached this way.
        $singularCollections = [
            'main', 'circle', 'banner', 'trending_destination', 'best_sellers',
            'view_details_1', 'view_details_2', 'view_details_3',
        ];

        $touchedSingular = Media::whereIn('id', $mediaIds)
            ->whereIn('collection', $singularCollections)
            ->pluck('collection')
            ->unique();

        if ($touchedSingular->isNotEmpty()) {
            Media::where('mediable_type', $model::class)
                ->where('mediable_id', $model->id)
                ->whereIn('collection', $touchedSingular)
                ->whereNotIn('id', $mediaIds)
                ->update(['mediable_type' => null, 'mediable_id' => null]);
        }

        Media::whereIn('id', $mediaIds)->update([
            'mediable_type' => $model::class,
            'mediable_id' => $model->id,
        ]);
    }
}

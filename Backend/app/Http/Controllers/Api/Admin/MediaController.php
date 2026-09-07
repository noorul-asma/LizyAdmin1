<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    /**
     * Media Library listing - every uploaded file across every module,
     * newest first, with optional search/type/module/attachment filters.
     * Powers the standalone /media admin page; the per-module Add/Edit
     * forms upload straight through store() below instead and never call
     * this.
     */
    public function index(Request $request)
    {
        $query = Media::query()->with('mediable')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }
        if ($request->filled('collection')) {
            $query->where('collection', $request->get('collection'));
        }
        if ($request->filled('q')) {
            $query->where(function ($sub) use ($request) {
                $term = $request->get('q');
                $sub->where('original_name', 'like', "%{$term}%")
                    ->orWhere('alt_text', 'like', "%{$term}%");
            });
        }
        if ($request->filled('module') && isset(Media::MODULE_MAP[$request->get('module')])) {
            $query->where('mediable_type', Media::MODULE_MAP[$request->get('module')]);
        }
        if ($request->boolean('unattached')) {
            $query->whereNull('mediable_id');
        }

        $perPage = min((int) $request->get('per_page', 24), 100);

        return MediaResource::collection($query->paginate($perPage));
    }

    /**
     * Upload one or more files. When `mediable_module` + `mediable_id` are
     * given, each file is attached directly to that Product/Property/
     * TourPackage/Service - this is how the Media Library page's "+ Add
     * Media" form links an upload to a specific item. Left out, the file
     * comes back "unattached" (mediable_type/id null) and its id is meant
     * to be sent back as `media_ids` when creating/updating a record from
     * that module's own Add/Edit form instead.
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif,mp4,mov,pdf,doc,docx'],
            'collection' => ['nullable', 'string', 'in:main,gallery,documents,circle,banner,trending_destination,best_sellers,view_details_1,view_details_2,view_details_3'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'mediable_module' => ['nullable', 'string', 'in:'.implode(',', array_keys(Media::MODULE_MAP))],
            'mediable_id' => ['required_with:mediable_module', 'nullable', 'integer'],
        ], [
            // The default "The files.0 failed to upload." fires when PHP
            // itself rejects the file before this validation rule (or
            // anything else in this controller) ever runs - almost always
            // because the file is larger than php.ini's upload_max_filesize
            // (WAMP defaults to just 2MB) or post_max_size. The 20MB rule
            // above never even gets a chance to apply in that case, so a
            // clearer message here is what actually helps: increase
            // upload_max_filesize and post_max_size in php.ini (both the
            // one Apache/WAMP uses AND the one `php artisan serve` uses,
            // if that's how the admin is being run locally - they can be
            // two different files) to something like 20M, then restart the
            // server. This message never fires for a legitimately-too-big
            // file under the 20MB cap - that still gets Laravel's normal
            // "must not be greater than 20480 kilobytes" message instead.
            'files.*.uploaded' => 'This image is too large for the server to accept — it was rejected before upload even started. Increase upload_max_filesize and post_max_size in php.ini (WAMP default is only 2MB) to at least 20M, then restart the server and try again.',
        ]);

        [$mediableType, $mediableId] = $this->resolveMediable($request);

        $created = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store('media/'.date('Y/m'), 'public');
            $mime = $file->getClientMimeType();
            $type = match (true) {
                str_starts_with($mime, 'image/') => 'image',
                str_starts_with($mime, 'video/') => 'video',
                default => 'document',
            };

            $created[] = Media::create([
                'mediable_type' => $mediableType,
                'mediable_id' => $mediableId,
                'type' => $type,
                'collection' => $request->get('collection', 'gallery'),
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size' => $file->getSize(),
                'alt_text' => $request->get('alt_text'),
                'sort_order' => 0,
            ]);
        }

        return MediaResource::collection(collect($created));
    }

    public function update(Request $request, int $id)
    {
        $media = Media::findOrFail($id);

        $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'collection' => ['nullable', 'string', 'in:main,gallery,documents,circle,banner,trending_destination,best_sellers,view_details_1,view_details_2,view_details_3'],
            'sort_order' => ['nullable', 'integer'],
            'mediable_module' => ['nullable', 'string', 'in:'.implode(',', array_keys(Media::MODULE_MAP))],
            'mediable_id' => ['required_with:mediable_module', 'nullable', 'integer'],
        ]);

        $data = $request->only(['alt_text', 'collection', 'sort_order']);

        if ($request->has('mediable_module')) {
            [$data['mediable_type'], $data['mediable_id']] = $this->resolveMediable($request);
        }

        $media->update($data);

        return new MediaResource($media->fresh('mediable'));
    }

    /** Turns the request's friendly `mediable_module` key + `mediable_id` into a validated [class, id] pair (or [null, null] when no module was given). */
    private function resolveMediable(Request $request): array
    {
        if (! $request->filled('mediable_module')) {
            return [null, null];
        }

        $modelClass = Media::MODULE_MAP[$request->get('mediable_module')];
        $mediableId = (int) $request->get('mediable_id');

        if (! $modelClass::whereKey($mediableId)->exists()) {
            throw ValidationException::withMessages(['mediable_id' => 'The selected item was not found.']);
        }

        return [$modelClass, $mediableId];
    }

    public function destroy(int $id)
    {
        $media = Media::findOrFail($id);
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return response()->json(['message' => 'Media deleted.']);
    }
}

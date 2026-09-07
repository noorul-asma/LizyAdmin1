<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->with(['children', 'media']);

        if ($request->filled('module')) {
            $query->module($request->get('module'));
        }
        if ($request->boolean('root_only')) {
            $query->root();
        }

        return CategoryResource::collection($query->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $category = Category::create($data);

        if ($request->filled('media_ids')) {
            $this->syncBanners($category, $request->input('media_ids'));
        }

        return new CategoryResource($category->fresh('media'));
    }

    public function show(int $id)
    {
        return new CategoryResource(Category::with(['children', 'media'])->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->validate($this->rules($id)));

        if ($request->filled('media_ids')) {
            $this->syncBanners($category, $request->input('media_ids'));
        }

        return new CategoryResource($category->fresh('media'));
    }

    public function destroy(int $id)
    {
        Category::findOrFail($id)->delete();

        return response()->json(['message' => 'Category deleted.']);
    }

    /**
     * Attaches uploaded banner images (collection=gallery) to this
     * category. Purely additive - unlike a single-image slot (main),
     * gallery images are never auto-detached when new ones are added,
     * matching how Package Gallery already behaves elsewhere. Removing
     * a specific banner is done from the Media Library directly.
     */
    private function syncBanners(Category $category, array $mediaIds): void
    {
        Media::whereIn('id', $mediaIds)->update([
            'mediable_type' => Category::class,
            'mediable_id' => $category->id,
        ]);
    }

    private function rules(?int $id = null): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'module' => ['required', 'string', 'in:'.implode(',', \App\Models\Category::MODULES)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

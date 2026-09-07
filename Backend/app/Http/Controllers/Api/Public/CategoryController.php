<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /** Category tree for one module, e.g. GET /api/public/categories?module=product */
    public function index(Request $request)
    {
        $request->validate(['module' => ['required', 'string', 'in:'.implode(',', Category::MODULES)]]);

        $categories = Category::query()
            ->module($request->get('module'))
            ->active()
            ->root()
            ->with(['children' => fn ($q) => $q->active(), 'media'])
            ->orderBy('sort_order')
            ->get();

        return CategoryResource::collection($categories);
    }
}

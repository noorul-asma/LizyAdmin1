<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query()->where('is_active', true);

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->get('parent_id'));
        } else {
            $query->whereNull('parent_id');
        }

        return LocationResource::collection($query->orderBy('name')->get());
    }
}

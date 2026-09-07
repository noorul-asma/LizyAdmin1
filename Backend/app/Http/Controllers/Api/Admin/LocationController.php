<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query()->with('children');

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->get('parent_id'));
        } elseif ($request->boolean('root_only')) {
            $query->whereNull('parent_id');
        }

        return LocationResource::collection($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $location = Location::create($data);

        return new LocationResource($location);
    }

    public function show(int $id)
    {
        return new LocationResource(Location::with('children')->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $location = Location::findOrFail($id);
        $location->update($request->validate($this->rules($id)));

        return new LocationResource($location);
    }

    public function destroy(int $id)
    {
        Location::findOrFail($id)->delete();

        return response()->json(['message' => 'Location deleted.']);
    }

    private function rules(?int $id = null): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:locations,id'],
            'type' => ['required', 'string', 'in:country,state,city,area'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

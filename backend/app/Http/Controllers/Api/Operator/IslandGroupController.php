<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\IslandGroupRequest;
use App\Http\Resources\IslandGroupResource;
use App\Models\IslandGroup;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IslandGroupController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return IslandGroupResource::collection(IslandGroup::query()->with('islands:id,name')->orderBy('name')->get());
    }

    public function store(IslandGroupRequest $request): IslandGroupResource
    {
        $islandGroup = IslandGroup::query()->create([
            'name' => $request->validated('name'),
        ]);

        $islandGroup->islands()->sync($request->validated('island_ids', []));

        return new IslandGroupResource($islandGroup->load('islands:id,name'));
    }

    public function show(IslandGroup $islandGroup): IslandGroupResource
    {
        return new IslandGroupResource($islandGroup->load('islands:id,name'));
    }

    public function update(IslandGroupRequest $request, IslandGroup $islandGroup): IslandGroupResource
    {
        $islandGroup->update([
            'name' => $request->validated('name'),
        ]);

        if ($request->has('island_ids')) {
            $islandGroup->islands()->sync($request->validated('island_ids', []));
        }

        return new IslandGroupResource($islandGroup->refresh()->load('islands:id,name'));
    }

    public function destroy(IslandGroup $islandGroup)
    {
        $islandGroup->delete();

        return response()->noContent();
    }
}

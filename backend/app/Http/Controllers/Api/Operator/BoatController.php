<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\BoatRequest;
use App\Http\Resources\BoatResource;
use App\Models\Boat;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BoatController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BoatResource::collection(Boat::query()->with(['transportProvider:id,name', 'islandGroups:id,name'])->orderBy('name')->get());
    }

    public function store(BoatRequest $request): BoatResource
    {
        $boat = Boat::query()->create($request->safe()->except('island_group_ids'));
        $boat->islandGroups()->sync($request->validated('island_group_ids', []));

        return new BoatResource($boat->load(['transportProvider:id,name', 'islandGroups:id,name']));
    }

    public function show(Boat $boat): BoatResource
    {
        return new BoatResource($boat->load(['transportProvider:id,name', 'islandGroups:id,name']));
    }

    public function update(BoatRequest $request, Boat $boat): BoatResource
    {
        $boat->update($request->safe()->except('island_group_ids'));

        if ($request->has('island_group_ids')) {
            $boat->islandGroups()->sync($request->validated('island_group_ids', []));
        }

        return new BoatResource($boat->refresh()->load(['transportProvider:id,name', 'islandGroups:id,name']));
    }

    public function destroy(Boat $boat)
    {
        $boat->delete();

        return response()->noContent();
    }
}

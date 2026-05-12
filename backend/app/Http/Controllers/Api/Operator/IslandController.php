<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\IslandRequest;
use App\Http\Resources\IslandResource;
use App\Models\Island;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IslandController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return IslandResource::collection(Island::query()->with('atoll:id,code,name')->orderBy('name')->get());
    }

    public function store(IslandRequest $request): IslandResource
    {
        $island = Island::query()->create($request->validated());

        return new IslandResource($island->load('atoll:id,code,name'));
    }

    public function show(Island $island): IslandResource
    {
        return new IslandResource($island->load('atoll:id,code,name'));
    }

    public function update(IslandRequest $request, Island $island): IslandResource
    {
        $island->update($request->validated());

        return new IslandResource($island->refresh()->load('atoll:id,code,name'));
    }

    public function destroy(Island $island)
    {
        $island->delete();

        return response()->noContent();
    }
}

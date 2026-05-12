<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\AtollRequest;
use App\Http\Resources\AtollResource;
use App\Models\Atoll;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AtollController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AtollResource::collection(Atoll::query()->orderBy('name')->get());
    }

    public function store(AtollRequest $request): AtollResource
    {
        return new AtollResource(Atoll::query()->create($request->validated()));
    }

    public function show(Atoll $atoll): AtollResource
    {
        return new AtollResource($atoll);
    }

    public function update(AtollRequest $request, Atoll $atoll): AtollResource
    {
        $atoll->update($request->validated());

        return new AtollResource($atoll->refresh());
    }

    public function destroy(Atoll $atoll)
    {
        $atoll->delete();

        return response()->noContent();
    }
}

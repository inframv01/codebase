<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\TransportProviderRequest;
use App\Http\Resources\TransportProviderResource;
use App\Models\TransportProvider;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransportProviderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TransportProviderResource::collection(TransportProvider::query()->orderBy('name')->get());
    }

    public function store(TransportProviderRequest $request): TransportProviderResource
    {
        return new TransportProviderResource(TransportProvider::query()->create($request->validated()));
    }

    public function show(TransportProvider $transportProvider): TransportProviderResource
    {
        return new TransportProviderResource($transportProvider);
    }

    public function update(TransportProviderRequest $request, TransportProvider $transportProvider): TransportProviderResource
    {
        $transportProvider->update($request->validated());

        return new TransportProviderResource($transportProvider->refresh());
    }

    public function destroy(TransportProvider $transportProvider)
    {
        $transportProvider->delete();

        return response()->noContent();
    }
}

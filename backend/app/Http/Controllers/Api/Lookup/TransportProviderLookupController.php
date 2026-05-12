<?php

namespace App\Http\Controllers\Api\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransportProviderResource;
use App\Models\TransportProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransportProviderLookupController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return TransportProviderResource::collection(
            TransportProvider::query()
                ->where('active', true)
                ->when($request->integer('island_id'), function ($query, $islandId): void {
                    $query->whereHas('boats.islandGroups.islands', fn ($islandQuery) => $islandQuery->where('islands.id', $islandId));
                })
                ->orderBy('name')
                ->get()
        );
    }
}

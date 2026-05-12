<?php

namespace App\Http\Controllers\Api\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Resources\IslandResource;
use App\Models\Island;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IslandLookupController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return IslandResource::collection(
            Island::query()
                ->with('atoll:id,code,name')
                ->when($request->integer('atoll_id'), fn ($query, $atollId) => $query->where('atoll_id', $atollId))
                ->orderBy('name')
                ->get()
        );
    }
}

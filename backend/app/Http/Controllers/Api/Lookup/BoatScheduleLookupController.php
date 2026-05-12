<?php

namespace App\Http\Controllers\Api\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoatScheduleResource;
use App\Models\BoatSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BoatScheduleLookupController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return BoatScheduleResource::collection(
            BoatSchedule::query()
                ->with(['boat:id,name', 'originIsland:id,name', 'destinationIsland:id,name'])
                ->when($request->integer('island_id'), fn ($query, $islandId) => $query->where('destination_island_id', $islandId))
                ->when($request->date('from'), fn ($query, $from) => $query->where('departs_at', '>=', $from))
                ->when($request->date('to'), fn ($query, $to) => $query->where('departs_at', '<=', $to))
                ->orderBy('departs_at')
                ->get()
        );
    }
}

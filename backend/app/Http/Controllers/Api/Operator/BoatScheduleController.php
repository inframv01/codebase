<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\BoatScheduleRequest;
use App\Http\Resources\BoatScheduleResource;
use App\Models\Boat;
use App\Models\BoatSchedule;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BoatScheduleController extends Controller
{
    public function index(Boat $boat): AnonymousResourceCollection
    {
        return BoatScheduleResource::collection(
            $boat->schedules()->with(['boat:id,name', 'originIsland:id,name', 'destinationIsland:id,name'])->orderBy('departs_at')->get()
        );
    }

    public function store(BoatScheduleRequest $request, Boat $boat): BoatScheduleResource
    {
        $schedule = $boat->schedules()->create($request->validated());

        return new BoatScheduleResource($schedule->load(['boat:id,name', 'originIsland:id,name', 'destinationIsland:id,name']));
    }

    public function show(Boat $boat, BoatSchedule $schedule): BoatScheduleResource
    {
        abort_unless($schedule->boat_id === $boat->id, 404);

        return new BoatScheduleResource($schedule->load(['boat:id,name', 'originIsland:id,name', 'destinationIsland:id,name']));
    }

    public function update(BoatScheduleRequest $request, Boat $boat, BoatSchedule $schedule): BoatScheduleResource
    {
        abort_unless($schedule->boat_id === $boat->id, 404);

        $schedule->update($request->validated());

        return new BoatScheduleResource($schedule->refresh()->load(['boat:id,name', 'originIsland:id,name', 'destinationIsland:id,name']));
    }

    public function destroy(Boat $boat, BoatSchedule $schedule)
    {
        abort_unless($schedule->boat_id === $boat->id, 404);

        $schedule->delete();

        return response()->noContent();
    }
}

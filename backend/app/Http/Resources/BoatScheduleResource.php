<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoatScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'departs_at' => $this->departs_at?->toIso8601String(),
            'arrives_at' => $this->arrives_at?->toIso8601String(),
            'capacity_remaining_kg' => $this->capacity_remaining_kg,
            'boat' => $this->whenLoaded('boat', fn (): array => [
                'id' => $this->boat->id,
                'name' => $this->boat->name,
            ]),
            'origin_island' => $this->whenLoaded('originIsland', fn (): array => [
                'id' => $this->originIsland->id,
                'name' => $this->originIsland->name,
            ]),
            'destination_island' => $this->whenLoaded('destinationIsland', fn (): array => [
                'id' => $this->destinationIsland->id,
                'name' => $this->destinationIsland->name,
            ]),
        ];
    }
}

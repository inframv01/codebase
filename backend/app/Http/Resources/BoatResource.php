<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'capacity_kg' => $this->capacity_kg,
            'active' => $this->active,
            'transport_provider' => $this->whenLoaded('transportProvider', fn (): array => [
                'id' => $this->transportProvider->id,
                'name' => $this->transportProvider->name,
            ]),
            'island_groups' => $this->whenLoaded('islandGroups', fn () => $this->islandGroups->map(fn ($group): array => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values()),
        ];
    }
}

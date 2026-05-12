<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IslandGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'islands' => $this->whenLoaded('islands', fn () => $this->islands->map(fn ($island): array => [
                'id' => $island->id,
                'name' => $island->name,
            ])->values()),
        ];
    }
}

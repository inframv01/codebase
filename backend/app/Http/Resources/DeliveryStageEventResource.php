<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DeliveryStageEventResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stage' => $this->stage?->value,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'notes' => $this->notes,
            'actor' => $this->whenLoaded('actor', fn (): ?array => $this->actor ? [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
                'role' => $this->actor->role?->value,
            ] : null),
        ];
    }
}

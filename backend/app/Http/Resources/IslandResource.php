<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IslandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'atoll' => $this->whenLoaded('atoll', fn (): array => [
                'id' => $this->atoll->id,
                'code' => $this->atoll->code,
                'name' => $this->atoll->name,
            ]),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'id_card_number' => $this->id_card_number,
            'email' => $this->email,
            'role' => $this->role?->value,
            'house_name' => $this->house_name,
            'floor' => $this->floor,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'atoll' => $this->whenLoaded('atoll', fn (): ?array => $this->atoll ? [
                'id' => $this->atoll->id,
                'code' => $this->atoll->code,
                'name' => $this->atoll->name,
            ] : null),
            'island' => $this->whenLoaded('island', fn (): ?array => $this->island ? [
                'id' => $this->island->id,
                'name' => $this->island->name,
            ] : null),
            'contact_numbers' => $this->whenLoaded('contactNumbers', fn () => $this->contactNumbers->map(fn ($contact): array => [
                'id' => $contact->id,
                'number' => $contact->number,
                'position' => $contact->position,
            ])->values()),
        ];
    }
}

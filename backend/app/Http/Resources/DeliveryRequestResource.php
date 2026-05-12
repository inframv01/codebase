<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DeliveryRequestResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'status' => $this->status?->value,
            'current_stage' => $this->current_stage?->value,
            'requires_inspection' => $this->requires_inspection,
            'fixed_cost_cents' => $this->fixed_cost_cents,
            'variable_cost_cents' => $this->variable_cost_cents,
            'total_cost_cents' => $this->total_cost_cents,
            'quote_confirmed_at' => $this->quote_confirmed_at?->toIso8601String(),
            'notes' => $this->notes,
            'destination_island' => $this->whenLoaded('destinationIsland', fn (): ?array => $this->destinationIsland ? [
                'id' => $this->destinationIsland->id,
                'name' => $this->destinationIsland->name,
            ] : null),
            'transport_provider' => $this->whenLoaded('transportProvider', fn (): ?array => $this->transportProvider ? [
                'id' => $this->transportProvider->id,
                'name' => $this->transportProvider->name,
            ] : null),
            'boat_schedule' => $this->whenLoaded('boatSchedule', fn (): ?array => $this->boatSchedule ? [
                'id' => $this->boatSchedule->id,
                'status' => $this->boatSchedule->status,
                'departs_at' => $this->boatSchedule->departs_at?->toIso8601String(),
            ] : null),
            'details' => match ($this->type) {
                'post_office' => $this->whenLoaded('postOfficeDetail', fn () => $this->postOfficeDetail ? [
                    'tracking_number' => $this->postOfficeDetail->tracking_number,
                    'screenshot_path' => $this->postOfficeDetail->screenshot_path,
                ] : null),
                'male_address' => $this->whenLoaded('maleAddressDetail', fn () => $this->maleAddressDetail ? [
                    'address' => $this->maleAddressDetail->address,
                    'contact_name' => $this->maleAddressDetail->contact_name,
                    'contact_phone' => $this->maleAddressDetail->contact_phone,
                ] : null),
                'shop' => $this->whenLoaded('shopDetail', fn () => $this->shopDetail ? [
                    'shop_address' => $this->shopDetail->shop_address,
                    'contact_name' => $this->shopDetail->contact_name,
                    'contact_phone' => $this->shopDetail->contact_phone,
                    'quote_path' => $this->shopDetail->quote_path,
                    'items' => $this->shopDetail->items,
                ] : null),
            },
            'stage_events' => $this->whenLoaded('stageEvents', fn () => DeliveryStageEventResource::collection($this->stageEvents)),
            'payments' => $this->whenLoaded('payments', fn () => PaymentResource::collection($this->payments)),
        ];
    }
}

<?php

namespace App\Services;

use App\Enums\DeliveryStage;
use App\Enums\DeliveryStatus;
use App\Jobs\NotifyOperatorsOfNewDeliveryRequest;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryRequestService
{
    public function __construct(
        private readonly PricingResolver $pricingResolver,
        private readonly DeliveryStageService $deliveryStageService,
    ) {}

    public function create(User $user, Request $request, array $payload): DeliveryRequest
    {
        $deliveryRequest = DB::transaction(function () use ($user, $request, $payload): DeliveryRequest {
            $pricing = $this->pricingResolver->resolve(
                $payload['type'],
                (int) $payload['destination_island_id'],
                (float) $payload['weight_kg'],
            );

            $deliveryRequest = DeliveryRequest::query()->create([
                'user_id' => $user->id,
                'type' => $payload['type'],
                'destination_island_id' => $payload['destination_island_id'],
                'transport_provider_id' => $payload['transport_provider_id'] ?? null,
                'boat_schedule_id' => $payload['boat_schedule_id'] ?? null,
                'status' => $pricing['requires_inspection'] ? DeliveryStatus::PendingQuote : DeliveryStatus::AwaitingPayment,
                'current_stage' => null,
                'fixed_cost_cents' => $pricing['fixed_cost_cents'],
                'variable_cost_cents' => $pricing['variable_cost_cents'],
                'total_cost_cents' => $pricing['total_cost_cents'],
                'requires_inspection' => $pricing['requires_inspection'],
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->storeTypeSpecificDetails($deliveryRequest, $request, $payload);

            $this->deliveryStageService->append($deliveryRequest, DeliveryStage::Created->value, $user->id, 'Delivery request created.');

            if ($pricing['requires_inspection']) {
                $this->deliveryStageService->append($deliveryRequest, DeliveryStage::QuotePending->value, $user->id, 'Awaiting operator quote confirmation.');
            }

            return $deliveryRequest->fresh([
                'destinationIsland.atoll',
                'transportProvider',
                'boatSchedule.boat',
                'postOfficeDetail',
                'maleAddressDetail',
                'shopDetail',
                'stageEvents.actor',
                'payments',
            ]);
        });

        NotifyOperatorsOfNewDeliveryRequest::dispatch($deliveryRequest->id);

        return $deliveryRequest;
    }

    private function storeTypeSpecificDetails(DeliveryRequest $deliveryRequest, Request $request, array $payload): void
    {
        match ($payload['type']) {
            'post_office' => $deliveryRequest->postOfficeDetail()->create([
                'tracking_number' => $payload['tracking_number'],
                'screenshot_path' => $request->file('order_image')?->store(
                    'delivery-requests/'.$deliveryRequest->uuid.'/post-office',
                    'uploads'
                ),
            ]),
            'male_address' => $deliveryRequest->maleAddressDetail()->create([
                'address' => $payload['address'],
                'contact_name' => $payload['contact_name'],
                'contact_phone' => $payload['contact_phone'],
            ]),
            'shop' => $deliveryRequest->shopDetail()->create([
                'shop_address' => $payload['shop_address'],
                'contact_name' => $payload['contact_name'],
                'contact_phone' => $payload['contact_phone'],
                'quote_path' => $request->file('quote_copy')?->store(
                    'delivery-requests/'.$deliveryRequest->uuid.'/shop',
                    'uploads'
                ),
                'items' => $payload['items'] ?? null,
            ]),
        };
    }
}

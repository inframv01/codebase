<?php

namespace App\Services;

use App\Enums\DeliveryStage;
use App\Enums\DeliveryStatus;
use App\Models\DeliveryRequest;
use Illuminate\Validation\ValidationException;

class DeliveryStageService
{
    public function append(DeliveryRequest $deliveryRequest, string $stage, ?int $actorUserId = null, ?string $notes = null): void
    {
        $stageEnum = DeliveryStage::tryFrom($stage);

        if ($stageEnum === null) {
            throw ValidationException::withMessages([
                'stage' => 'The selected delivery stage is invalid.',
            ]);
        }

        if (in_array($stageEnum->value, DeliveryStage::operatorTransitValues(), true) && $deliveryRequest->accepted_by_operator_id === null) {
            throw ValidationException::withMessages([
                'stage' => 'The delivery request must be accepted by an operator before transit stages can be updated.',
            ]);
        }

        if ($deliveryRequest->current_stage !== null && $stageEnum !== DeliveryStage::Cancelled) {
            $currentIndex = array_search($deliveryRequest->current_stage->value, DeliveryStage::values(), true);
            $nextIndex = array_search($stageEnum->value, DeliveryStage::values(), true);

            if ($currentIndex !== false && $nextIndex !== false && $nextIndex <= $currentIndex) {
                throw ValidationException::withMessages([
                    'stage' => 'Delivery stages must move forward.',
                ]);
            }
        }

        $deliveryRequest->stageEvents()->create([
            'stage' => $stageEnum,
            'occurred_at' => now(),
            'actor_user_id' => $actorUserId,
            'notes' => $notes,
        ]);

        $deliveryRequest->forceFill([
            'current_stage' => $stageEnum,
            'status' => $this->statusForStage($stageEnum, $deliveryRequest->status),
        ])->save();
    }

    private function statusForStage(DeliveryStage $stage, DeliveryStatus $fallback): DeliveryStatus
    {
        return match ($stage) {
            DeliveryStage::QuotePending => DeliveryStatus::PendingQuote,
            DeliveryStage::QuoteConfirmed => DeliveryStatus::AwaitingPayment,
            DeliveryStage::PaymentUploaded => DeliveryStatus::PaymentReview,
            DeliveryStage::PaymentVerified, DeliveryStage::AcceptedByOperator => DeliveryStatus::Accepted,
            DeliveryStage::PickedUp, DeliveryStage::InTransit, DeliveryStage::ArrivedAtIsland, DeliveryStage::OutForDelivery => DeliveryStatus::InTransit,
            DeliveryStage::Delivered => DeliveryStatus::Delivered,
            DeliveryStage::Cancelled => DeliveryStatus::Cancelled,
            default => $fallback,
        };
    }
}

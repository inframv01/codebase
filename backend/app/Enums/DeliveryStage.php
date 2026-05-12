<?php

namespace App\Enums;

enum DeliveryStage: string
{
    case Created = 'created';
    case QuotePending = 'quote_pending';
    case QuoteConfirmed = 'quote_confirmed';
    case PaymentUploaded = 'payment_uploaded';
    case PaymentVerified = 'payment_verified';
    case AcceptedByOperator = 'accepted_by_operator';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case ArrivedAtIsland = 'arrived_at_island';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_map(fn (self $stage): string => $stage->value, self::cases());
    }

    public static function operatorTransitValues(): array
    {
        return [
            self::PickedUp->value,
            self::InTransit->value,
            self::ArrivedAtIsland->value,
            self::OutForDelivery->value,
            self::Delivered->value,
        ];
    }
}

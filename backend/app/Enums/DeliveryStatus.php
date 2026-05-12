<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PendingQuote = 'pending_quote';
    case AwaitingPayment = 'awaiting_payment';
    case PaymentReview = 'payment_review';
    case Accepted = 'accepted';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}

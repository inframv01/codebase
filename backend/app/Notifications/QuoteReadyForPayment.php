<?php

namespace App\Notifications;

use App\Models\DeliveryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteReadyForPayment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DeliveryRequest $deliveryRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_request_uuid' => $this->deliveryRequest->uuid,
            'message' => 'Your final quote is ready. You can now upload your payment slip.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Quote ready for payment')
            ->line('Your final quote is ready. You can now upload your payment slip.')
            ->line('Request: '.$this->deliveryRequest->uuid);
    }
}

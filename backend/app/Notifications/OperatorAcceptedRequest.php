<?php

namespace App\Notifications;

use App\Models\DeliveryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OperatorAcceptedRequest extends Notification implements ShouldQueue
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
            'message' => 'An operator has accepted your delivery request.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Delivery request accepted')
            ->line('An operator has accepted your delivery request.')
            ->line('Request: '.$this->deliveryRequest->uuid);
    }
}

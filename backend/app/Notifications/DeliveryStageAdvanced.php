<?php

namespace App\Notifications;

use App\Models\DeliveryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStageAdvanced extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DeliveryRequest $deliveryRequest,
        private readonly string $stage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_request_uuid' => $this->deliveryRequest->uuid,
            'stage' => $this->stage,
            'message' => 'Your delivery request has moved to the next stage.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Delivery stage updated')
            ->line('Your delivery request has been updated to stage: '.$this->stage)
            ->line('Request: '.$this->deliveryRequest->uuid);
    }
}

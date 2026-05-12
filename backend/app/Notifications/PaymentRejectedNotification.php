<?php

namespace App\Notifications;

use App\Models\DeliveryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DeliveryRequest $deliveryRequest,
        private readonly ?string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_request_uuid' => $this->deliveryRequest->uuid,
            'message' => 'Your payment slip was rejected.',
            'reason' => $this->reason,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('Payment rejected')
            ->line('Your payment slip was rejected. Please upload a new bank transfer slip.')
            ->line('Request: '.$this->deliveryRequest->uuid);

        if ($this->reason !== null) {
            $mailMessage->line('Reason: '.$this->reason);
        }

        return $mailMessage;
    }
}

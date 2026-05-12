<?php

namespace App\Jobs;

use App\Models\DeliveryRequest;
use App\Models\User;
use App\Notifications\NewDeliveryRequestForOperator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyOperatorsOfNewDeliveryRequest implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $deliveryRequestId,
    ) {}

    public function handle(): void
    {
        $deliveryRequest = DeliveryRequest::query()->find($this->deliveryRequestId);

        if ($deliveryRequest === null) {
            return;
        }

        User::query()
            ->whereIn('role', ['operator', 'admin'])
            ->cursor()
            ->each(fn (User $operator) => $operator->notify(new NewDeliveryRequestForOperator($deliveryRequest)));
    }
}

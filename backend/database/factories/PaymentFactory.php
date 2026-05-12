<?php

namespace Database\Factories;

use App\Models\DeliveryRequest;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'delivery_request_id' => DeliveryRequest::factory(),
            'amount_cents' => 3000,
            'slip_path' => 'payments/slip.png',
            'status' => 'pending',
        ];
    }
}

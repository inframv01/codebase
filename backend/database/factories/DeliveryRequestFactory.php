<?php

namespace Database\Factories;

use App\Models\DeliveryRequest;
use App\Models\Island;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryRequest>
 */
class DeliveryRequestFactory extends Factory
{
    protected $model = DeliveryRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'male_address',
            'destination_island_id' => Island::factory(),
            'status' => 'awaiting_payment',
            'current_stage' => 'created',
            'fixed_cost_cents' => 2500,
            'variable_cost_cents' => 500,
            'total_cost_cents' => 3000,
            'requires_inspection' => false,
            'notes' => fake()->sentence(),
        ];
    }
}

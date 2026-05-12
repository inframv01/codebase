<?php

namespace Database\Factories;

use App\Models\TransportProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransportProvider>
 */
class TransportProviderFactory extends Factory
{
    protected $model = TransportProvider::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'type' => fake()->randomElement(['boat', 'inter_island', 'inter_atoll']),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'active' => true,
        ];
    }
}

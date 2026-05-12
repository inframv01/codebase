<?php

namespace Database\Factories;

use App\Models\Boat;
use App\Models\TransportProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Boat>
 */
class BoatFactory extends Factory
{
    protected $model = Boat::class;

    public function definition(): array
    {
        return [
            'transport_provider_id' => TransportProvider::factory(),
            'name' => fake()->unique()->firstName().' Ferry',
            'capacity_kg' => fake()->randomFloat(2, 100, 1000),
            'active' => true,
        ];
    }
}

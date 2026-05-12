<?php

namespace Database\Factories;

use App\Models\Atoll;
use App\Models\Island;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Island>
 */
class IslandFactory extends Factory
{
    protected $model = Island::class;

    public function definition(): array
    {
        return [
            'atoll_id' => Atoll::factory(),
            'name' => fake()->unique()->city(),
        ];
    }
}

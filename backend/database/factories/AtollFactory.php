<?php

namespace Database\Factories;

use App\Models\Atoll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Atoll>
 */
class AtollFactory extends Factory
{
    protected $model = Atoll::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('??'),
            'name' => fake()->unique()->city().' Atoll',
        ];
    }
}

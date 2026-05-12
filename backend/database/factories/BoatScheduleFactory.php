<?php

namespace Database\Factories;

use App\Models\Boat;
use App\Models\BoatSchedule;
use App\Models\Island;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoatSchedule>
 */
class BoatScheduleFactory extends Factory
{
    protected $model = BoatSchedule::class;

    public function definition(): array
    {
        $departsAt = fake()->dateTimeBetween('+1 day', '+5 days');
        $arrivesAt = (clone $departsAt)->modify('+6 hours');

        return [
            'boat_id' => Boat::factory(),
            'origin_island_id' => Island::factory(),
            'destination_island_id' => Island::factory(),
            'departs_at' => $departsAt,
            'arrives_at' => $arrivesAt,
            'status' => 'scheduled',
            'capacity_remaining_kg' => fake()->randomFloat(2, 50, 500),
        ];
    }
}

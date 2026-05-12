<?php

namespace Database\Factories;

use App\Models\SavedAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedAddress>
 */
class SavedAddressFactory extends Factory
{
    protected $model = SavedAddress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->words(2, true),
            'purpose' => fake()->randomElement(['drop_off', 'pickup']),
            'address' => ['line_1' => fake()->streetAddress(), 'island' => fake()->city()],
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'is_default' => false,
        ];
    }
}

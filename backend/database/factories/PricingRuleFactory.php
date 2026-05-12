<?php

namespace Database\Factories;

use App\Models\Island;
use App\Models\PricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingRule>
 */
class PricingRuleFactory extends Factory
{
    protected $model = PricingRule::class;

    public function definition(): array
    {
        return [
            'scope_type' => 'island',
            'scope_id' => Island::factory(),
            'service_type' => fake()->randomElement(['post_office', 'male_address', 'shop']),
            'fixed_cost_cents' => fake()->numberBetween(1500, 5000),
            'variable_rate_cents_per_kg' => fake()->numberBetween(100, 500),
            'min_charge_cents' => fake()->numberBetween(1500, 6000),
            'requires_inspection' => false,
            'active' => true,
        ];
    }
}

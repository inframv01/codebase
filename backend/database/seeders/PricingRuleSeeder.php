<?php

namespace Database\Seeders;

use App\Models\Island;
use App\Models\IslandGroup;
use App\Models\PricingRule;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        $male = Island::query()->where('name', 'Male')->first();
        $centralGroup = IslandGroup::query()->where('name', 'Central Islands')->first();

        if ($male !== null) {
            foreach (['post_office', 'male_address', 'shop'] as $serviceType) {
                PricingRule::query()->updateOrCreate(
                    [
                        'scope_type' => 'island',
                        'scope_id' => $male->id,
                        'service_type' => $serviceType,
                    ],
                    [
                        'fixed_cost_cents' => 2500,
                        'variable_rate_cents_per_kg' => 200,
                        'min_charge_cents' => 2500,
                        'requires_inspection' => false,
                        'active' => true,
                    ]
                );
            }
        }

        if ($centralGroup !== null) {
            PricingRule::query()->updateOrCreate(
                [
                    'scope_type' => 'island_group',
                    'scope_id' => $centralGroup->id,
                    'service_type' => 'shop',
                ],
                [
                    'fixed_cost_cents' => 3000,
                    'variable_rate_cents_per_kg' => 250,
                    'min_charge_cents' => 3200,
                    'requires_inspection' => true,
                    'active' => true,
                ]
            );
        }
    }
}

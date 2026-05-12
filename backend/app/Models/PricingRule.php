<?php

namespace App\Models;

use Database\Factories\PricingRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'scope_type',
    'scope_id',
    'service_type',
    'fixed_cost_cents',
    'variable_rate_cents_per_kg',
    'min_charge_cents',
    'requires_inspection',
    'active',
])]
class PricingRule extends Model
{
    /** @use HasFactory<PricingRuleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requires_inspection' => 'boolean',
            'active' => 'boolean',
        ];
    }
}

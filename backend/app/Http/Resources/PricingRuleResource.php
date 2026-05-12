<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scope_type' => $this->scope_type,
            'scope_id' => $this->scope_id,
            'service_type' => $this->service_type,
            'fixed_cost_cents' => $this->fixed_cost_cents,
            'variable_rate_cents_per_kg' => $this->variable_rate_cents_per_kg,
            'min_charge_cents' => $this->min_charge_cents,
            'requires_inspection' => $this->requires_inspection,
            'active' => $this->active,
        ];
    }
}

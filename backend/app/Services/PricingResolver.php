<?php

namespace App\Services;

use App\Models\Island;
use App\Models\PricingRule;
use Illuminate\Validation\ValidationException;

class PricingResolver
{
    public function resolve(string $serviceType, int $islandId, float $weightKg): array
    {
        $island = Island::query()->with('groups:id')->findOrFail($islandId);

        $rule = PricingRule::query()
            ->where('service_type', $serviceType)
            ->where('active', true)
            ->where(function ($query) use ($island): void {
                $query
                    ->where(function ($islandQuery) use ($island): void {
                        $islandQuery
                            ->where('scope_type', 'island')
                            ->where('scope_id', $island->id);
                    })
                    ->orWhere(function ($groupQuery) use ($island): void {
                        $groupQuery
                            ->where('scope_type', 'island_group')
                            ->whereIn('scope_id', $island->groups->pluck('id'));
                    });
            })
            ->orderByRaw("CASE WHEN scope_type = 'island' THEN 0 ELSE 1 END")
            ->first();

        if ($rule === null) {
            throw ValidationException::withMessages([
                'destination_island_id' => 'No pricing rule is configured for this island and service type.',
            ]);
        }

        $variableCost = $rule->requires_inspection
            ? null
            : (int) round($weightKg * (int) $rule->variable_rate_cents_per_kg);

        $minimumCharge = (int) $rule->min_charge_cents;
        $fixedCost = (int) $rule->fixed_cost_cents;
        $candidateTotal = $variableCost === null ? null : $fixedCost + $variableCost;
        $totalCost = $candidateTotal === null ? null : max($candidateTotal, $minimumCharge);

        return [
            'fixed_cost_cents' => $fixedCost,
            'variable_cost_cents' => $variableCost,
            'min_charge_cents' => $minimumCharge,
            'total_cost_cents' => $totalCost,
            'requires_inspection' => (bool) $rule->requires_inspection,
            'pricing_rule_id' => $rule->id,
        ];
    }
}

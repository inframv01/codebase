<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\PricingRuleRequest;
use App\Http\Resources\PricingRuleResource;
use App\Models\PricingRule;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class PricingRuleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PricingRuleResource::collection(PricingRule::query()->latest()->get());
    }

    public function store(PricingRuleRequest $request): PricingRuleResource
    {
        $payload = $this->validatedPayload($request->validated());

        return new PricingRuleResource(PricingRule::query()->create($payload));
    }

    public function show(PricingRule $pricingRule): PricingRuleResource
    {
        return new PricingRuleResource($pricingRule);
    }

    public function update(PricingRuleRequest $request, PricingRule $pricingRule): PricingRuleResource
    {
        $pricingRule->update($this->validatedPayload($request->validated()));

        return new PricingRuleResource($pricingRule->refresh());
    }

    public function destroy(PricingRule $pricingRule)
    {
        $pricingRule->delete();

        return response()->noContent();
    }

    private function validatedPayload(array $payload): array
    {
        $table = $payload['scope_type'] === 'island' ? 'islands' : 'island_groups';

        if (! \DB::table($table)->whereKey($payload['scope_id'])->exists()) {
            throw ValidationException::withMessages([
                'scope_id' => 'The selected scope does not exist.',
            ]);
        }

        return $payload;
    }
}

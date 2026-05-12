<?php

namespace App\Http\Controllers\Api\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lookup\QuotePreviewRequest;
use App\Services\PricingResolver;
use Illuminate\Http\JsonResponse;

class QuotePreviewController extends Controller
{
    public function __invoke(QuotePreviewRequest $request, PricingResolver $pricingResolver): JsonResponse
    {
        $payload = $request->validated();

        return response()->json(
            $pricingResolver->resolve(
                $payload['type'],
                (int) $payload['destination_island_id'],
                (float) $payload['weight_kg'],
            )
        );
    }
}

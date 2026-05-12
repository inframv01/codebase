<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreDeliveryRequestRequest;
use App\Http\Resources\DeliveryRequestResource;
use App\Models\DeliveryRequest;
use App\Notifications\DeliveryStageAdvanced;
use App\Services\DeliveryRequestService;
use App\Services\DeliveryStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class DeliveryRequestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));

        return DeliveryRequestResource::collection(
            $request->user()->deliveryRequests()
                ->with(['destinationIsland.atoll', 'transportProvider', 'boatSchedule.boat', 'stageEvents.actor', 'payments'])
                ->latest()
                ->paginate($perPage)
        );
    }

    public function store(StoreDeliveryRequestRequest $request, DeliveryRequestService $deliveryRequestService): DeliveryRequestResource
    {
        return new DeliveryRequestResource($deliveryRequestService->create($request->user(), $request, $request->validated()));
    }

    public function show(Request $request, DeliveryRequest $deliveryRequest): DeliveryRequestResource
    {
        Gate::authorize('viewCustomer', $deliveryRequest);

        return new DeliveryRequestResource($deliveryRequest->load([
            'destinationIsland.atoll',
            'transportProvider',
            'boatSchedule.boat',
            'postOfficeDetail',
            'maleAddressDetail',
            'shopDetail',
            'stageEvents.actor',
            'payments',
        ]));
    }

    public function cancel(Request $request, DeliveryRequest $deliveryRequest, DeliveryStageService $deliveryStageService): JsonResponse
    {
        Gate::authorize('cancel', $deliveryRequest);
        abort_if(
            in_array($deliveryRequest->status, [DeliveryStatus::InTransit, DeliveryStatus::Delivered, DeliveryStatus::Cancelled], true),
            422,
            'This delivery request can no longer be cancelled.'
        );

        $deliveryStageService->append($deliveryRequest, 'cancelled', $request->user()->id, 'Cancelled by user.');
        $request->user()->notify(new DeliveryStageAdvanced($deliveryRequest->fresh(), 'cancelled'));

        return response()->json([
            'message' => 'Delivery request cancelled successfully.',
            'delivery_request' => new DeliveryRequestResource($deliveryRequest->fresh(['stageEvents.actor', 'payments'])),
        ]);
    }
}

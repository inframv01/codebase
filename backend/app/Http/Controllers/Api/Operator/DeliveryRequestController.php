<?php

namespace App\Http\Controllers\Api\Operator;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\DeliveryQuoteRequest;
use App\Http\Requests\Operator\DeliveryStageRequest;
use App\Http\Requests\Operator\PaymentDecisionRequest;
use App\Http\Resources\DeliveryRequestResource;
use App\Http\Resources\PaymentResource;
use App\Models\DeliveryRequest;
use App\Models\Payment;
use App\Notifications\DeliveryStageAdvanced;
use App\Notifications\OperatorAcceptedRequest;
use App\Notifications\PaymentRejectedNotification;
use App\Notifications\PaymentVerifiedNotification;
use App\Notifications\QuoteReadyForPayment;
use App\Services\DeliveryStageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeliveryRequestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAnyOperator', DeliveryRequest::class);

        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));

        return DeliveryRequestResource::collection(
            DeliveryRequest::query()
                ->with(['user', 'destinationIsland.atoll', 'transportProvider', 'boatSchedule.boat', 'stageEvents.actor', 'payments'])
                ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
                ->when($request->user()->role !== UserRole::Admin, function ($query) use ($request): void {
                    $query->where(function ($visibilityQuery) use ($request): void {
                        $visibilityQuery
                            ->whereNull('accepted_by_operator_id')
                            ->orWhere('accepted_by_operator_id', $request->user()->id);
                    });
                })
                ->latest()
                ->paginate($perPage)
        );
    }

    public function quote(DeliveryQuoteRequest $request, DeliveryRequest $deliveryRequest, DeliveryStageService $deliveryStageService): JsonResponse
    {
        Gate::authorize('quote', $deliveryRequest);

        $deliveryRequest->update([
            'variable_cost_cents' => $request->validated('variable_cost_cents'),
            'total_cost_cents' => $request->validated('total_cost_cents'),
            'quote_confirmed_at' => now(),
            'status' => DeliveryStatus::AwaitingPayment,
        ]);

        $deliveryStageService->append($deliveryRequest, 'quote_confirmed', $request->user()->id, $request->validated('notes'));
        $deliveryRequest->user->notify(new QuoteReadyForPayment($deliveryRequest->fresh()));

        return response()->json([
            'message' => 'Quote confirmed successfully.',
            'delivery_request' => new DeliveryRequestResource($deliveryRequest->fresh(['stageEvents.actor', 'payments'])),
        ]);
    }

    public function accept(Request $request, DeliveryRequest $deliveryRequest, DeliveryStageService $deliveryStageService): JsonResponse
    {
        Gate::authorize('accept', $deliveryRequest);

        if ($deliveryRequest->accepted_by_operator_id !== null && $deliveryRequest->accepted_by_operator_id !== $request->user()->id) {
            throw new AuthorizationException('This delivery request has already been accepted by another operator.');
        }

        $deliveryRequest->update([
            'accepted_by_operator_id' => $request->user()->id,
        ]);

        $deliveryStageService->append($deliveryRequest, 'accepted_by_operator', $request->user()->id, 'Accepted by operator.');
        $deliveryRequest->user->notify(new OperatorAcceptedRequest($deliveryRequest->fresh()));

        return response()->json([
            'message' => 'Delivery request accepted successfully.',
            'delivery_request' => new DeliveryRequestResource($deliveryRequest->fresh(['stageEvents.actor', 'payments'])),
        ]);
    }

    public function stage(DeliveryStageRequest $request, DeliveryRequest $deliveryRequest, DeliveryStageService $deliveryStageService): JsonResponse
    {
        Gate::authorize('stage', $deliveryRequest);

        $deliveryStageService->append($deliveryRequest, $request->validated('stage'), $request->user()->id, $request->validated('notes'));
        $deliveryRequest->user->notify(new DeliveryStageAdvanced($deliveryRequest->fresh(), $request->validated('stage')));

        return response()->json([
            'message' => 'Delivery stage updated successfully.',
            'delivery_request' => new DeliveryRequestResource($deliveryRequest->fresh(['stageEvents.actor', 'payments'])),
        ]);
    }

    public function verifyPayment(Request $request, DeliveryRequest $deliveryRequest, Payment $payment, DeliveryStageService $deliveryStageService): JsonResponse
    {
        Gate::authorize('verifyPayment', $deliveryRequest);
        abort_unless($payment->delivery_request_id === $deliveryRequest->id, 404);

        DB::transaction(function () use ($request, $deliveryRequest, $payment, $deliveryStageService): void {
            $payment->update([
                'status' => 'verified',
                'verified_by_user_id' => $request->user()->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $deliveryStageService->append($deliveryRequest, 'payment_verified', $request->user()->id, 'Payment verified by operator.');
        });

        $deliveryRequest->user->notify(new PaymentVerifiedNotification($deliveryRequest->fresh()));

        return response()->json([
            'message' => 'Payment verified successfully.',
            'payment' => new PaymentResource($payment->fresh()),
        ]);
    }

    public function rejectPayment(PaymentDecisionRequest $request, DeliveryRequest $deliveryRequest, Payment $payment): JsonResponse
    {
        Gate::authorize('rejectPayment', $deliveryRequest);
        abort_unless($payment->delivery_request_id === $deliveryRequest->id, 404);

        $payment->update([
            'status' => 'rejected',
            'verified_by_user_id' => $request->user()->id,
            'verified_at' => now(),
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        $deliveryRequest->update([
            'status' => DeliveryStatus::AwaitingPayment,
        ]);

        $deliveryRequest->user->notify(new PaymentRejectedNotification($deliveryRequest->fresh(), $request->validated('rejection_reason')));

        return response()->json([
            'message' => 'Payment rejected successfully.',
            'payment' => new PaymentResource($payment->fresh()),
        ]);
    }
}

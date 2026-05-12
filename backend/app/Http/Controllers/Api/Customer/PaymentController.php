<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\PaymentUploadRequest;
use App\Http\Resources\PaymentResource;
use App\Models\DeliveryRequest;
use App\Services\DeliveryStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(PaymentUploadRequest $request, DeliveryRequest $deliveryRequest, DeliveryStageService $deliveryStageService): JsonResponse
    {
        Gate::authorize('uploadPayment', $deliveryRequest);

        if ($deliveryRequest->status !== DeliveryStatus::AwaitingPayment || $deliveryRequest->total_cost_cents === null) {
            throw ValidationException::withMessages([
                'payment' => 'This delivery request is not ready for payment upload.',
            ]);
        }

        if ((int) $request->validated('amount_cents') !== (int) $deliveryRequest->total_cost_cents) {
            throw ValidationException::withMessages([
                'amount_cents' => 'The uploaded payment amount must match the quoted total.',
            ]);
        }

        if ($deliveryRequest->payments()->whereIn('status', ['pending', 'verified'])->exists()) {
            throw ValidationException::withMessages([
                'payment' => 'A payment is already under review or has been verified for this request.',
            ]);
        }

        $payment = DB::transaction(function () use ($request, $deliveryRequest, $deliveryStageService) {
            $payment = $deliveryRequest->payments()->create([
                'amount_cents' => $request->validated('amount_cents'),
                'slip_path' => $request->file('slip')->store('payments/'.$deliveryRequest->uuid, 'uploads'),
                'status' => 'pending',
            ]);

            $deliveryStageService->append($deliveryRequest, 'payment_uploaded', $request->user()->id, 'Payment slip uploaded by customer.');

            return $payment;
        });

        return response()->json([
            'message' => 'Payment slip uploaded successfully.',
            'payment' => new PaymentResource($payment),
        ], 201);
    }
}

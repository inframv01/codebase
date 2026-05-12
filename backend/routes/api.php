<?php

use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Customer\DeliveryRequestController as CustomerDeliveryRequestController;
use App\Http\Controllers\Api\Customer\MeController;
use App\Http\Controllers\Api\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Api\Customer\SavedAddressController;
use App\Http\Controllers\Api\Lookup\AtollLookupController;
use App\Http\Controllers\Api\Lookup\BoatScheduleLookupController;
use App\Http\Controllers\Api\Lookup\IslandLookupController;
use App\Http\Controllers\Api\Lookup\QuotePreviewController;
use App\Http\Controllers\Api\Lookup\TransportProviderLookupController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Operator\AtollController;
use App\Http\Controllers\Api\Operator\BoatController;
use App\Http\Controllers\Api\Operator\BoatScheduleController;
use App\Http\Controllers\Api\Operator\DeliveryRequestController as OperatorDeliveryRequestController;
use App\Http\Controllers\Api\Operator\IslandController;
use App\Http\Controllers\Api\Operator\IslandGroupController;
use App\Http\Controllers\Api\Operator\PricingRuleController;
use App\Http\Controllers\Api\Operator\TransportProviderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->middleware('throttle:auth')->group(function (): void {
        Route::post('/register', RegisterController::class);
        Route::post('/login', LoginController::class);
        Route::get('/google/redirect', [GoogleAuthController::class, 'redirect']);
        Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
        Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
        Route::post('/reset-password', [PasswordResetController::class, 'reset']);

        Route::middleware('throttle:otp')->group(function (): void {
            Route::post('/otp/resend', [OtpController::class, 'resend']);
            Route::post('/otp/verify', [OtpController::class, 'verify']);
        });
    });

    Route::get('/health', fn () => ['status' => 'ok']);

    Route::middleware('throttle:api')->group(function (): void {
        Route::get('/lookups/atolls', AtollLookupController::class);
        Route::get('/lookups/islands', IslandLookupController::class);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::get('/me', [MeController::class, 'show']);
        Route::patch('/me', [MeController::class, 'update']);
        Route::post('/me/contact-numbers', [MeController::class, 'replaceContactNumbers']);
        Route::get('/lookups/transport-providers', TransportProviderLookupController::class);
        Route::get('/lookups/boats/schedules', BoatScheduleLookupController::class);
        Route::post('/quotes/preview', QuotePreviewController::class);

        Route::apiResource('addresses', SavedAddressController::class);
        Route::apiResource('delivery-requests', CustomerDeliveryRequestController::class)->only(['index', 'show', 'store'])->parameters(['delivery-requests' => 'deliveryRequest']);
        Route::post('/delivery-requests/{deliveryRequest}/cancel', [CustomerDeliveryRequestController::class, 'cancel']);
        Route::post('/delivery-requests/{deliveryRequest}/payments', [CustomerPaymentController::class, 'store']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'read']);
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);

        Route::prefix('operator')->middleware(['role:operator,admin', 'abilities:operator'])->group(function (): void {
            Route::apiResource('atolls', AtollController::class);
            Route::apiResource('islands', IslandController::class);
            Route::apiResource('island-groups', IslandGroupController::class)->parameters(['island-groups' => 'island_group']);
            Route::apiResource('transport-providers', TransportProviderController::class)->parameters(['transport-providers' => 'transport_provider']);
            Route::apiResource('boats', BoatController::class);
            Route::apiResource('boats.schedules', BoatScheduleController::class)->scoped(['schedule' => 'id']);
            Route::apiResource('pricing-rules', PricingRuleController::class)->parameters(['pricing-rules' => 'pricing_rule']);
            Route::get('delivery-requests', [OperatorDeliveryRequestController::class, 'index']);
            Route::post('delivery-requests/{deliveryRequest}/quote', [OperatorDeliveryRequestController::class, 'quote']);
            Route::post('delivery-requests/{deliveryRequest}/accept', [OperatorDeliveryRequestController::class, 'accept']);
            Route::post('delivery-requests/{deliveryRequest}/stage', [OperatorDeliveryRequestController::class, 'stage']);
            Route::post('delivery-requests/{deliveryRequest}/payments/{payment}/verify', [OperatorDeliveryRequestController::class, 'verifyPayment']);
            Route::post('delivery-requests/{deliveryRequest}/payments/{payment}/reject', [OperatorDeliveryRequestController::class, 'rejectPayment']);
        });
    });
});

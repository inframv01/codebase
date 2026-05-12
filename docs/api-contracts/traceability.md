# Traceability Matrix

This matrix links each contract domain to backend source-of-truth files used.

## Auth

- Routes: `backend/routes/api.php` (`/api/v1/auth/*`)
- Controllers:
  - `backend/app/Http/Controllers/Api/Auth/RegisterController.php`
  - `backend/app/Http/Controllers/Api/Auth/LoginController.php`
  - `backend/app/Http/Controllers/Api/Auth/GoogleAuthController.php`
  - `backend/app/Http/Controllers/Api/Auth/PasswordResetController.php`
  - `backend/app/Http/Controllers/Api/Auth/OtpController.php`
- Requests:
  - `backend/app/Http/Requests/Auth/RegisterRequest.php`
  - `backend/app/Http/Requests/Auth/LoginRequest.php`
  - `backend/app/Http/Requests/Auth/ForgotPasswordRequest.php`
  - `backend/app/Http/Requests/Auth/ResetPasswordRequest.php`
  - `backend/app/Http/Requests/Auth/ResendOtpRequest.php`
  - `backend/app/Http/Requests/Auth/VerifyOtpRequest.php`
- Services:
  - `backend/app/Services/OtpService.php`

## Me/Profile

- Routes: `backend/routes/api.php` (`/api/v1/me*`)
- Controller: `backend/app/Http/Controllers/Api/Customer/MeController.php`
- Request validators:
  - `backend/app/Http/Requests/Customer/MeUpdateRequest.php`
  - `backend/app/Http/Requests/Customer/ContactNumbersRequest.php`
- Resource: `backend/app/Http/Resources/UserResource.php`

## Lookups/Quote

- Routes: `backend/routes/api.php` (`/api/v1/lookups/*`, `/api/v1/quotes/preview`)
- Controllers:
  - `backend/app/Http/Controllers/Api/Lookup/AtollLookupController.php`
  - `backend/app/Http/Controllers/Api/Lookup/IslandLookupController.php`
  - `backend/app/Http/Controllers/Api/Lookup/TransportProviderLookupController.php`
  - `backend/app/Http/Controllers/Api/Lookup/BoatScheduleLookupController.php`
  - `backend/app/Http/Controllers/Api/Lookup/QuotePreviewController.php`
- Request validators:
  - `backend/app/Http/Requests/Lookup/QuotePreviewRequest.php`
- Resources:
  - `backend/app/Http/Resources/AtollResource.php`
  - `backend/app/Http/Resources/IslandResource.php`
  - `backend/app/Http/Resources/TransportProviderResource.php`
  - `backend/app/Http/Resources/BoatScheduleResource.php`
- Service:
  - `backend/app/Services/PricingResolver.php`

## Addresses

- Routes: `backend/routes/api.php` (`/api/v1/addresses*`)
- Controller: `backend/app/Http/Controllers/Api/Customer/SavedAddressController.php`
- Request: `backend/app/Http/Requests/Customer/SavedAddressRequest.php`
- Resource: `backend/app/Http/Resources/SavedAddressResource.php`

## Customer Delivery/Payment

- Routes: `backend/routes/api.php` (`/api/v1/delivery-requests*`)
- Controllers:
  - `backend/app/Http/Controllers/Api/Customer/DeliveryRequestController.php`
  - `backend/app/Http/Controllers/Api/Customer/PaymentController.php`
- Requests:
  - `backend/app/Http/Requests/Customer/StoreDeliveryRequestRequest.php`
  - `backend/app/Http/Requests/Customer/PaymentUploadRequest.php`
- Resources:
  - `backend/app/Http/Resources/DeliveryRequestResource.php`
  - `backend/app/Http/Resources/PaymentResource.php`
  - `backend/app/Http/Resources/DeliveryStageEventResource.php`
- Services/Policy:
  - `backend/app/Services/DeliveryRequestService.php`
  - `backend/app/Policies/DeliveryRequestPolicy.php`

## Notifications

- Routes: `backend/routes/api.php` (`/api/v1/notifications*`)
- Controller: `backend/app/Http/Controllers/Api/NotificationController.php`
- Resource: `backend/app/Http/Resources/DatabaseNotificationResource.php`

## Operator

- Routes: `backend/routes/api.php` (`/api/v1/operator/*`)
- Controllers:
  - `backend/app/Http/Controllers/Api/Operator/AtollController.php`
  - `backend/app/Http/Controllers/Api/Operator/IslandController.php`
  - `backend/app/Http/Controllers/Api/Operator/IslandGroupController.php`
  - `backend/app/Http/Controllers/Api/Operator/TransportProviderController.php`
  - `backend/app/Http/Controllers/Api/Operator/BoatController.php`
  - `backend/app/Http/Controllers/Api/Operator/BoatScheduleController.php`
  - `backend/app/Http/Controllers/Api/Operator/PricingRuleController.php`
  - `backend/app/Http/Controllers/Api/Operator/DeliveryRequestController.php`
- Requests:
  - `backend/app/Http/Requests/Operator/AtollRequest.php`
  - `backend/app/Http/Requests/Operator/IslandRequest.php`
  - `backend/app/Http/Requests/Operator/IslandGroupRequest.php`
  - `backend/app/Http/Requests/Operator/TransportProviderRequest.php`
  - `backend/app/Http/Requests/Operator/BoatRequest.php`
  - `backend/app/Http/Requests/Operator/BoatScheduleRequest.php`
  - `backend/app/Http/Requests/Operator/PricingRuleRequest.php`
  - `backend/app/Http/Requests/Operator/DeliveryQuoteRequest.php`
  - `backend/app/Http/Requests/Operator/DeliveryStageRequest.php`
  - `backend/app/Http/Requests/Operator/PaymentDecisionRequest.php`
- Resources:
  - `backend/app/Http/Resources/AtollResource.php`
  - `backend/app/Http/Resources/IslandResource.php`
  - `backend/app/Http/Resources/IslandGroupResource.php`
  - `backend/app/Http/Resources/TransportProviderResource.php`
  - `backend/app/Http/Resources/BoatResource.php`
  - `backend/app/Http/Resources/BoatScheduleResource.php`
  - `backend/app/Http/Resources/PricingRuleResource.php`
  - `backend/app/Http/Resources/DeliveryRequestResource.php`
  - `backend/app/Http/Resources/PaymentResource.php`
- Policy:
  - `backend/app/Policies/DeliveryRequestPolicy.php`

## Global Error Renderer

- `backend/bootstrap/app.php`

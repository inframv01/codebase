# Frontend Quickstart Mapping

This maps backend contracts to initial React API modules.

## Suggested API Modules

- `authApi`
  - register, login, googleRedirect, googleCallback, forgotPassword, resetPassword, resendOtp, verifyOtp
  - Source contract: `auth.md`

- `meApi`
  - getMe, updateMe, replaceContactNumbers
  - Source contract: `me-profile.md`

- `lookupApi`
  - getAtolls, getIslands, getTransportProviders, getBoatSchedules, previewQuote
  - Source contract: `lookups-and-quotes.md`

- `addressApi`
  - listAddresses, createAddress, getAddress, updateAddress, deleteAddress
  - Source contract: `addresses.md`

- `deliveryApi`
  - listDeliveryRequests, createDeliveryRequest, getDeliveryRequest, cancelDeliveryRequest, uploadPaymentSlip
  - Source contract: `delivery-requests.md`

- `notificationApi`
  - listNotificationsPaginated, markNotificationRead, markAllNotificationsRead
  - Source contract: `notifications.md`

- `operatorApi`
  - atoll/island/island-group/transport-provider/boat/boat-schedule CRUD
  - listOperatorDeliveryRequests, quoteRequest, acceptRequest, stageRequest, verifyPayment, rejectPayment
  - pricing rule CRUD
  - Source contract: `operator.md`

## Shared Frontend Types To Define First

- `ApiValidationError` for HTTP 422
- `ApiMessageResponse` for `{ message: string }`
- `PaginatedResponse<T>` for notifications and operator delivery list endpoints
- `DeliveryRequest`, `Payment`, `User`, `Atoll`, `Island`, `IslandGroup`, `TransportProvider`, `Boat`, `BoatSchedule`, `PricingRule`

## Upload Endpoints

Use `FormData` for:
- `POST /api/v1/delivery-requests`
- `POST /api/v1/delivery-requests/{deliveryRequest}/payments`

## Auth Header Injection

Attach `Authorization: Bearer <token>` for all endpoints except auth and health.

# Frontend API Contract Pack

Code-derived API contract for the current Laravel backend.

- Base URL prefix: `/api/v1`
- Auth for protected endpoints: `Authorization: Bearer <sanctum_token>`
- Content type:
  - `application/json` for most endpoints
  - `multipart/form-data` for file upload endpoints
- Date-time format: ISO8601 strings from `toIso8601String()` where resources define date fields
- Money units: integer cents for all `*_cents` fields

## Global Response And Error Contract

### Success envelopes
- Non-paginated single resource responses are unwrapped JSON objects.
- Non-paginated resource collection responses are unwrapped JSON arrays.
- Action responses use explicit controller JSON payloads (for example `{ "message": "...", ... }`).
- No-content deletes: HTTP `204` with empty body.

### Global exception JSON for API routes
Defined in `backend/bootstrap/app.php`.

- Non-validation API exceptions are rendered as:

```json
{
  "message": "..."
}
```

- Status code follows thrown exception status (for example `404`, `403`, `500`).

### Validation errors
Laravel validation errors return HTTP `422` with default Laravel validation shape:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Validation message"
    ]
  }
}
```

Controllers and services also throw `ValidationException::withMessages(...)`, which appears under the same `errors` map keys.

## Pagination Contract

Two endpoints are paginated:
- `GET /api/v1/notifications`
- `GET /api/v1/operator/delivery-requests`

They use Laravel paginator resource collections and return:
- `data`: item array
- `links`: pagination links object
- `meta`: pagination metadata (`current_page`, `last_page`, `per_page`, `total`, ...)

Query parameter supported by both:
- `per_page` integer, clamped to range `[1, 100]`, default `15`

All other list endpoints currently use `get()` (not paginator), so they return only `data` without `meta`/`links`.

## Auth And Authorization Layers

- Public auth routes: `throttle:auth` (and OTP routes additionally `throttle:otp`)
- Protected routes: `auth:sanctum` + `throttle:api`
- Operator routes: `role:operator,admin` and `abilities:operator`
- Some operator delivery/payment actions also enforce policy gates, returning `403` when denied.

## Domain Files

- `auth.md`
- `me-profile.md`
- `lookups-and-quotes.md`
- `addresses.md`
- `delivery-requests.md`
- `notifications.md`
- `operator.md`
- `frontend-quickstart.md`
- `traceability.md`

## Endpoint Coverage Index

### Auth
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/auth/google/redirect`
- `GET /api/v1/auth/google/callback`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/otp/resend`
- `POST /api/v1/auth/otp/verify`

### Health
- `GET /api/v1/health`

### Me/Profile
- `GET /api/v1/me`
- `PATCH /api/v1/me`
- `POST /api/v1/me/contact-numbers`

### Lookups And Quote Preview
- `GET /api/v1/lookups/atolls`
- `GET /api/v1/lookups/islands`
- `GET /api/v1/lookups/transport-providers`
- `GET /api/v1/lookups/boats/schedules`
- `POST /api/v1/quotes/preview`

### Addresses
- `GET /api/v1/addresses`
- `POST /api/v1/addresses`
- `GET /api/v1/addresses/{address}`
- `PUT|PATCH /api/v1/addresses/{address}`
- `DELETE /api/v1/addresses/{address}`

### Customer Delivery Requests And Payments
- `GET /api/v1/delivery-requests`
- `POST /api/v1/delivery-requests`
- `GET /api/v1/delivery-requests/{deliveryRequest}`
- `POST /api/v1/delivery-requests/{deliveryRequest}/cancel`
- `POST /api/v1/delivery-requests/{deliveryRequest}/payments`

### Notifications
- `GET /api/v1/notifications`
- `POST /api/v1/notifications/{notificationId}/read`
- `POST /api/v1/notifications/read-all`

### Operator CRUD And Workflow
- `GET|POST /api/v1/operator/atolls`
- `GET|PUT|PATCH|DELETE /api/v1/operator/atolls/{atoll}`
- `GET|POST /api/v1/operator/islands`
- `GET|PUT|PATCH|DELETE /api/v1/operator/islands/{island}`
- `GET|POST /api/v1/operator/island-groups`
- `GET|PUT|PATCH|DELETE /api/v1/operator/island-groups/{island_group}`
- `GET|POST /api/v1/operator/transport-providers`
- `GET|PUT|PATCH|DELETE /api/v1/operator/transport-providers/{transport_provider}`
- `GET|POST /api/v1/operator/boats`
- `GET|PUT|PATCH|DELETE /api/v1/operator/boats/{boat}`
- `GET|POST /api/v1/operator/boats/{boat}/schedules`
- `GET|PUT|PATCH|DELETE /api/v1/operator/boats/{boat}/schedules/{schedule}`
- `GET /api/v1/operator/delivery-requests`
- `POST /api/v1/operator/delivery-requests/{deliveryRequest}/quote`
- `POST /api/v1/operator/delivery-requests/{deliveryRequest}/accept`
- `POST /api/v1/operator/delivery-requests/{deliveryRequest}/stage`
- `POST /api/v1/operator/delivery-requests/{deliveryRequest}/payments/{payment}/verify`
- `POST /api/v1/operator/delivery-requests/{deliveryRequest}/payments/{payment}/reject`
- `GET|POST /api/v1/operator/pricing-rules`
- `GET|PUT|PATCH|DELETE /api/v1/operator/pricing-rules/{pricing_rule}`

## Traceability Sources

- Routes and middleware: `backend/routes/api.php`
- Global API error renderer: `backend/bootstrap/app.php`
- Validation rules: `backend/app/Http/Requests/**`
- Success payload serializers: `backend/app/Http/Resources/**`
- Action responses and statuses: `backend/app/Http/Controllers/Api/**`
- Quote preview payload structure: `backend/app/Services/PricingResolver.php`

# Customer Delivery Requests And Payments API Contract

All endpoints in this file require:
- `auth:sanctum`
- `throttle:api`

Delivery request path parameter uses route key `uuid`:
- `{deliveryRequest}` is a UUID string, not numeric id.

Payment path parameter in nested operator routes also uses UUID (`Payment::getRouteKeyName`).

## DeliveryRequestResource Shape

```json
{
  "uuid": "018f...",
  "type": "post_office",
  "status": "awaiting_payment",
  "current_stage": "payment_uploaded",
  "requires_inspection": false,
  "fixed_cost_cents": 2500,
  "variable_cost_cents": 1200,
  "total_cost_cents": 3700,
  "quote_confirmed_at": "2026-05-12T10:00:00+00:00",
  "notes": "...",
  "destination_island": {
    "id": 10,
    "name": "Dhidhdhoo"
  },
  "transport_provider": {
    "id": 3,
    "name": "North Ferry"
  },
  "boat_schedule": {
    "id": 7,
    "status": "scheduled",
    "departs_at": "2026-05-13T06:00:00+00:00"
  },
  "details": {},
  "stage_events": [],
  "payments": []
}
```

`details` polymorphic by `type`:
- `post_office`: `tracking_number`, `screenshot_path`
- `male_address`: `address`, `contact_name`, `contact_phone`
- `shop`: `shop_address`, `contact_name`, `contact_phone`, `quote_path`, `items`

## PaymentResource Shape

```json
{
  "uuid": "018f...",
  "amount_cents": 3700,
  "slip_path": "payments/.../slip.jpg",
  "status": "pending",
  "verified_at": null,
  "rejection_reason": null
}
```

## GET /api/v1/delivery-requests

- Success `200`:

```json
[
  {
    "uuid": "018f...",
    "type": "shop"
  }
]
```

- Errors:
  - `401` unauthenticated

## POST /api/v1/delivery-requests

- Content type: `multipart/form-data` (supports file fields)
- Base request fields:
  - `type` required enum: `post_office | male_address | shop`
  - `destination_island_id` required integer exists islands.id
  - `transport_provider_id` nullable integer exists transport_providers.id
  - `boat_schedule_id` nullable integer exists boat_schedules.id
  - `weight_kg` required numeric `> 0`
  - `notes` nullable string
  - `tracking_number` nullable string max 255
  - `order_image` nullable file mimes jpg/jpeg/png/pdf max 5120 KB
  - `address` nullable object/array
  - `contact_name` nullable string max 255
  - `contact_phone` nullable string max 30
  - `shop_address` nullable object/array
  - `quote_copy` nullable file mimes jpg/jpeg/png/pdf max 5120 KB
  - `items` nullable array

- Conditional validation (`after()`):
  - If `type = post_office`:
    - `tracking_number` required
    - `order_image` required file
  - If `type = male_address`:
    - `address`, `contact_name`, `contact_phone` required
  - If `type = shop`:
    - `shop_address`, `contact_name`, `contact_phone` required
    - exactly one of:
      - `quote_copy` file
      - non-empty `items` array

- Success `200` (`DeliveryRequestResource`):

```json
{
  "uuid": "018f...",
  "type": "post_office",
  "status": "pending_quote",
  "requires_inspection": true
}
```

- Status behavior on create:
  - `pending_quote` when pricing requires inspection
  - `awaiting_payment` when immediate quote is available

- Errors:
  - `422` validation errors
  - `422` no pricing rule for island/service
  - `404` destination island not found in resolver

## GET /api/v1/delivery-requests/{deliveryRequest}

- Path params:
  - `{deliveryRequest}` UUID
- Success `200` (`DeliveryRequestResource` with expanded relations)
- Errors:
  - `404` not found
  - `404` exists but belongs to different user (ownership abort)

## POST /api/v1/delivery-requests/{deliveryRequest}/cancel

- Path params:
  - `{deliveryRequest}` UUID
- Request body: none
- Success `200`:

```json
{
  "message": "Delivery request cancelled successfully.",
  "delivery_request": {
    "uuid": "018f...",
    "status": "cancelled"
  }
}
```

- Errors:
  - `404` not found or not owned
  - `422` cannot cancel when status is one of `in_transit | delivered | cancelled`

## POST /api/v1/delivery-requests/{deliveryRequest}/payments

- Path params:
  - `{deliveryRequest}` UUID
- Content type: `multipart/form-data`
- Request body:
  - `amount_cents` required integer min 0
  - `slip` required file mimes jpg/jpeg/png/pdf max 5120 KB
- Success `201`:

```json
{
  "message": "Payment slip uploaded successfully.",
  "payment": {
    "uuid": "018f...",
    "amount_cents": 3700,
    "status": "pending"
  }
}
```

- Errors:
  - `403` when policy `uploadPayment` denies (not owner)
  - `422` request not payable yet (`errors.payment`)
  - `422` `amount_cents` does not match quote total
  - `422` pending/verified payment already exists
  - `422` validation errors

## Pagination

No endpoints in this file are paginated.

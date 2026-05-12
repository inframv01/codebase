# Operator API Contract

All endpoints in this file require:
- `auth:sanctum`
- `throttle:api`
- `role:operator,admin`
- `abilities:operator`

Additional policy gates apply to operator delivery workflow endpoints (can return `403`).

## Shared Operator Resource Shapes

### AtollResource

```json
{ "id": 1, "code": "HA", "name": "Haa Alif" }
```

### IslandResource

```json
{
  "id": 10,
  "name": "Dhidhdhoo",
  "atoll": { "id": 1, "code": "HA", "name": "Haa Alif" }
}
```

### IslandGroupResource

```json
{
  "id": 3,
  "name": "North Group",
  "islands": [
    { "id": 10, "name": "Dhidhdhoo" }
  ]
}
```

### TransportProviderResource

```json
{
  "id": 5,
  "name": "North Ferry",
  "type": "boat",
  "contact_name": "...",
  "contact_phone": "...",
  "active": true
}
```

### BoatResource

```json
{
  "id": 2,
  "name": "Sea Runner",
  "capacity_kg": "500.00",
  "active": true,
  "transport_provider": { "id": 5, "name": "North Ferry" },
  "island_groups": [{ "id": 3, "name": "North Group" }]
}
```

### BoatScheduleResource

```json
{
  "id": 7,
  "status": "scheduled",
  "departs_at": "2026-05-13T06:00:00+00:00",
  "arrives_at": "2026-05-13T12:00:00+00:00",
  "capacity_remaining_kg": "120.00",
  "boat": { "id": 2, "name": "Sea Runner" },
  "origin_island": { "id": 1, "name": "Male" },
  "destination_island": { "id": 10, "name": "Dhidhdhoo" }
}
```

### PricingRuleResource

```json
{
  "id": 9,
  "scope_type": "island",
  "scope_id": 10,
  "service_type": "shop",
  "fixed_cost_cents": 2500,
  "variable_rate_cents_per_kg": 120,
  "min_charge_cents": 3000,
  "requires_inspection": false,
  "active": true
}
```

## Atolls

### GET /api/v1/operator/atolls
- Success `200`: `[AtollResource]`

### POST /api/v1/operator/atolls
- Body: `code` required unique max 10, `name` required max 255
- Success `200`: `AtollResource`
- Errors: `422`

### GET /api/v1/operator/atolls/{atoll}
- Path: `{atoll}` numeric id
- Success `200`: `AtollResource`
- Errors: `404`

### PUT|PATCH /api/v1/operator/atolls/{atoll}
- Body: same as create (`code` unique ignores current row)
- Success `200`: `{ "data": AtollResource }`
- Errors: `404`, `422`

### DELETE /api/v1/operator/atolls/{atoll}
- Success `204` empty

## Islands

### GET /api/v1/operator/islands
- Success `200`: `[IslandResource]`

### POST /api/v1/operator/islands
- Body:
  - `atoll_id` required integer exists atolls.id
  - `name` required string max 255 unique within selected atoll
- Success `200`: `IslandResource`
- Errors: `422`

### GET /api/v1/operator/islands/{island}
- Success `200`: `{ "data": IslandResource }`
- Errors: `404`

### PUT|PATCH /api/v1/operator/islands/{island}
- Body: same as create (name uniqueness ignores current row)
- Success `200`
- Errors: `404`, `422`

### DELETE /api/v1/operator/islands/{island}
- Success `204`

## Island Groups

### GET /api/v1/operator/island-groups
- Success `200`: `[IslandGroupResource]`

### POST /api/v1/operator/island-groups
- Body:
  - `name` required unique max 255
  - `island_ids` optional array
  - `island_ids.*` integer exists islands.id
- Success `200`: `IslandGroupResource`
- Errors: `422`

### GET /api/v1/operator/island-groups/{island_group}
- Success `200`
- Errors: `404`

### PUT|PATCH /api/v1/operator/island-groups/{island_group}
- Body: same as create (`island_ids` optional; if omitted in PATCH, relation sync is skipped)
- Success `200`
- Errors: `404`, `422`

### DELETE /api/v1/operator/island-groups/{island_group}
- Success `204`

## Transport Providers

### GET /api/v1/operator/transport-providers
- Success `200`: `[TransportProviderResource]`

### POST /api/v1/operator/transport-providers
- Body:
  - `name` required unique max 255
  - `type` required enum: `boat | inter_island | inter_atoll`
  - `contact_name` nullable string max 255
  - `contact_phone` nullable string max 30
  - `active` optional boolean
- Success `200`: `TransportProviderResource`
- Errors: `422`

### GET /api/v1/operator/transport-providers/{transport_provider}
- Success `200`
- Errors: `404`

### PUT|PATCH /api/v1/operator/transport-providers/{transport_provider}
- Body: same as create (`name` uniqueness ignores current row)
- Success `200`
- Errors: `404`, `422`

### DELETE /api/v1/operator/transport-providers/{transport_provider}
- Success `204`

## Boats

### GET /api/v1/operator/boats
- Success `200`: `[BoatResource]`

### POST /api/v1/operator/boats
- Body:
  - `transport_provider_id` required integer exists transport_providers.id
  - `name` required string max 255
  - `capacity_kg` nullable numeric `> 0`
  - `active` optional boolean
  - `island_group_ids` optional array
  - `island_group_ids.*` integer exists island_groups.id
- Success `200`: `BoatResource`
- Errors: `422`

### GET /api/v1/operator/boats/{boat}
- Success `200`
- Errors: `404`

### PUT|PATCH /api/v1/operator/boats/{boat}
- Body: same as create (`island_group_ids` optional; if omitted in PATCH, existing links are kept)
- Success `200`
- Errors: `404`, `422`

### DELETE /api/v1/operator/boats/{boat}
- Success `204`

## Boat Schedules

Route params:
- `{boat}` numeric id
- `{schedule}` numeric id

### GET /api/v1/operator/boats/{boat}/schedules
- Success `200`: `[BoatScheduleResource]`

### POST /api/v1/operator/boats/{boat}/schedules
- Body:
  - `origin_island_id` required integer exists islands.id
  - `destination_island_id` required integer exists islands.id and different from origin
  - `departs_at` required date
  - `arrives_at` required date after departs_at
  - `status` optional enum: `scheduled | departed | arrived | cancelled`
  - `capacity_remaining_kg` nullable numeric `>= 0`
- Success `200`: `BoatScheduleResource`
- Errors: `404`, `422`

### GET /api/v1/operator/boats/{boat}/schedules/{schedule}
- Success `200`
- Errors:
  - `404` when schedule not found
  - `404` when schedule exists but not under provided boat

### PUT|PATCH /api/v1/operator/boats/{boat}/schedules/{schedule}
- Body: same as create
- Success `200`
- Errors: `404`, `422`

### DELETE /api/v1/operator/boats/{boat}/schedules/{schedule}
- Success `204`
- Errors: `404` if schedule does not belong to boat

## Pricing Rules

### GET /api/v1/operator/pricing-rules
- Success `200`: `[PricingRuleResource]`

### POST /api/v1/operator/pricing-rules
- Body:
  - `scope_type` required enum: `island | island_group`
  - `scope_id` required integer min 1
  - `service_type` required enum: `post_office | male_address | shop`
  - `fixed_cost_cents` required integer min 0
  - `variable_rate_cents_per_kg` required integer min 0
  - `min_charge_cents` required integer min 0
  - `requires_inspection` optional boolean
  - `active` optional boolean
- Success `200`: `PricingRuleResource`
- Errors:
  - `422` validation errors
  - `422` invalid scope-id for selected scope-type (`errors.scope_id`)

### GET /api/v1/operator/pricing-rules/{pricing_rule}
- Success `200`
- Errors: `404`

### PUT|PATCH /api/v1/operator/pricing-rules/{pricing_rule}
- Body: same as create
- Success `200`
- Errors: `404`, `422`

### DELETE /api/v1/operator/pricing-rules/{pricing_rule}
- Success `204`

## Operator Delivery Workflow

Path params:
- `{deliveryRequest}` UUID
- `{payment}` UUID

### GET /api/v1/operator/delivery-requests
- Gate: `viewAnyOperator`
- Query:
  - `status` optional string filter
  - `per_page` optional integer, clamped `1..100`, default `15`
- Visibility behavior:
  - Admin sees all
  - Operator sees unassigned requests or requests already accepted by self
- Success `200` paginated `DeliveryRequestResource` collection:
  - includes `data`, `links`, `meta`
- Errors:
  - `403` gate denied

### POST /api/v1/operator/delivery-requests/{deliveryRequest}/quote
- Gate: `quote`
- Body:
  - `variable_cost_cents` required integer min 0
  - `total_cost_cents` required integer min 0
  - `notes` nullable string
- Success `200`:

```json
{
  "message": "Quote confirmed successfully.",
  "delivery_request": { "uuid": "018f...", "status": "awaiting_payment" }
}
```

- Errors: `403`, `404`, `422`

### POST /api/v1/operator/delivery-requests/{deliveryRequest}/accept
- Gate: `accept`
- Body: none
- Success `200`:

```json
{
  "message": "Delivery request accepted successfully.",
  "delivery_request": { "uuid": "018f...", "accepted_by_operator_id": 4 }
}
```

- Errors:
  - `403` gate denied
  - `403` explicit conflict when already accepted by another operator
  - `404` not found

### POST /api/v1/operator/delivery-requests/{deliveryRequest}/stage
- Gate: `stage`
- Body:
  - `stage` required enum:
    - `accepted_by_operator`
    - `picked_up`
    - `in_transit`
    - `arrived_at_island`
    - `out_for_delivery`
    - `delivered`
    - `cancelled`
  - `notes` nullable string
- Success `200` message + `delivery_request`
- Errors: `403`, `404`, `422`

### POST /api/v1/operator/delivery-requests/{deliveryRequest}/payments/{payment}/verify
- Gate: `verifyPayment`
- Body: none
- Success `200`:

```json
{
  "message": "Payment verified successfully.",
  "payment": { "uuid": "018f...", "status": "verified" }
}
```

- Errors:
  - `403` gate denied
  - `404` request/payment not found
  - `404` when payment does not belong to delivery request

### POST /api/v1/operator/delivery-requests/{deliveryRequest}/payments/{payment}/reject
- Gate: `rejectPayment`
- Body:
  - `rejection_reason` nullable string
- Success `200`:

```json
{
  "message": "Payment rejected successfully.",
  "payment": { "uuid": "018f...", "status": "rejected", "rejection_reason": "..." }
}
```

- Side effect: delivery request status is reset to `awaiting_payment`.
- Errors:
  - `403` gate denied
  - `404` request/payment not found
  - `404` when payment does not belong to delivery request
  - `422` validation errors

## Pagination

Paginated operator endpoint:
- `GET /api/v1/operator/delivery-requests`

All other list endpoints in this file are non-paginated and return only `data`.

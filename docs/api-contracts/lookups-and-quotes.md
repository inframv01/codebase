# Lookups And Quote Preview API Contract

All endpoints in this file use `throttle:api`.

Authentication requirements:
- `GET /lookups/atolls` and `GET /lookups/islands` are public.
- `GET /lookups/transport-providers`, `GET /lookups/boats/schedules`, and `POST /quotes/preview` require `auth:sanctum`.

## GET /api/v1/lookups/atolls

- Query: none
- Success `200` (`AtollResource` collection):

```json
[
  {
    "id": 1,
    "code": "HA",
    "name": "Haa Alif"
  }
]
```

## GET /api/v1/lookups/islands

- Query:
  - `atoll_id` optional integer
- Success `200` (`IslandResource` collection):

```json
[
  {
    "id": 10,
    "name": "Dhidhdhoo",
    "atoll": {
      "id": 1,
      "code": "HA",
      "name": "Haa Alif"
    }
  }
]
```

## GET /api/v1/lookups/transport-providers

- Query:
  - `island_id` optional integer
- Behavior:
  - only `active = true` providers returned
- Success `200` (`TransportProviderResource` collection):

```json
[
  {
    "id": 5,
    "name": "North Ferry",
    "type": "boat",
    "contact_name": "...",
    "contact_phone": "...",
    "active": true
  }
]
```

## GET /api/v1/lookups/boats/schedules

- Query:
  - `island_id` optional integer (matches destination island)
  - `from` optional date parseable by Laravel `Request::date()`
  - `to` optional date parseable by Laravel `Request::date()`
- Success `200` (`BoatScheduleResource` collection):

```json
[
  {
    "id": 7,
    "status": "scheduled",
    "departs_at": "2026-05-13T06:00:00+00:00",
    "arrives_at": "2026-05-13T12:00:00+00:00",
    "capacity_remaining_kg": "120.00",
    "boat": {
      "id": 2,
      "name": "Sea Runner"
    },
    "origin_island": {
      "id": 1,
      "name": "Male"
    },
    "destination_island": {
      "id": 10,
      "name": "Dhidhdhoo"
    }
  }
]
```

## POST /api/v1/quotes/preview

- Request body (`application/json`):
  - `type` required enum: `post_office | male_address | shop`
  - `destination_island_id` required integer exists islands.id
  - `weight_kg` required numeric `> 0`
- Success `200` (from `PricingResolver::resolve`):

```json
{
  "fixed_cost_cents": 2500,
  "variable_cost_cents": 1200,
  "min_charge_cents": 3000,
  "total_cost_cents": 3700,
  "requires_inspection": false,
  "pricing_rule_id": 9
}
```

- If `requires_inspection = true`, both `variable_cost_cents` and `total_cost_cents` are `null`.
- Errors:
  - `422` validation errors
  - `422` missing pricing rule: `errors.destination_island_id[0] = "No pricing rule is configured for this island and service type."`
  - `404` destination island not found in resolver (`findOrFail`)

## Pagination

No endpoints in this file are paginated.

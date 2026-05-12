# Saved Addresses API Contract

All endpoints in this file require:
- `auth:sanctum`
- `throttle:api`

Resource shape (`SavedAddressResource`):

```json
{
  "id": 1,
  "label": "Home",
  "purpose": "pickup",
  "address": {
    "line1": "..."
  },
  "contact_name": "...",
  "contact_phone": "...",
  "is_default": true
}
```

## GET /api/v1/addresses

- Success `200`:

```json
[
  {
    "id": 1,
    "label": "Home",
    "purpose": "pickup",
    "address": {},
    "contact_name": "...",
    "contact_phone": "...",
    "is_default": true
  }
]
```

## POST /api/v1/addresses

- Request body (`application/json`):
  - `label` required string max 255
  - `purpose` required enum: `drop_off | pickup`
  - `address` required object/array
  - `contact_name` required string max 255
  - `contact_phone` required string max 30
  - `is_default` optional boolean
- Success `200` (controller returns `SavedAddressResource` without explicit status override):

```json
{
  "id": 12,
  "label": "Office",
  "purpose": "drop_off",
  "address": {},
  "contact_name": "...",
  "contact_phone": "...",
  "is_default": false
}
```

- Behavior:
  - If `is_default = true`, other addresses for same user and same purpose are set to `is_default = false`.
- Errors:
  - `422` validation errors

## GET /api/v1/addresses/{address}

- Path params:
  - `address` integer id
- Success `200` (`SavedAddressResource`)
- Errors:
  - `404` if not found
  - `404` if record exists but belongs to another user (ownership check via `abort_unless`)

## PUT|PATCH /api/v1/addresses/{address}

- Path params:
  - `address` integer id
- Request body: same schema as `POST /addresses` (fields are required by request class)
- Success `200` (`SavedAddressResource`)
- Errors:
  - `404` not found or not owned by current user
  - `422` validation errors

## DELETE /api/v1/addresses/{address}

- Path params:
  - `address` integer id
- Success `204` empty body
- Errors:
  - `404` not found or not owned by current user

## Pagination

No endpoints in this file are paginated.

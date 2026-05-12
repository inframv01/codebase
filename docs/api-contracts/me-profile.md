# Me And Profile API Contract

All endpoints here require:
- `auth:sanctum`
- `throttle:api`

## GET /api/v1/health

- Auth: none
- Success `200`:

```json
{
  "status": "ok"
}
```

## GET /api/v1/me

- Request: none
- Success `200` (`UserResource`):

```json
{
  "id": 1,
  "name": "...",
  "id_card_number": "...",
  "email": "...",
  "role": "customer",
  "house_name": "...",
  "floor": "...",
  "email_verified_at": "2026-05-12T10:00:00+00:00",
  "atoll": {
    "id": 1,
    "code": "HA",
    "name": "Haa Alif"
  },
  "island": {
    "id": 12,
    "name": "Dhidhdhoo"
  },
  "contact_numbers": [
    {
      "id": 1,
      "number": "9607xxxxxx",
      "position": 1
    }
  ]
}
```

- Errors:
  - `401` unauthenticated

## PATCH /api/v1/me

- Request body (`application/json`), all fields optional (`sometimes`):
  - `name` string max 255
  - `id_card_number` string max 50 unique users.id_card_number ignore current user
  - `atoll_id` integer exists atolls.id
  - `island_id` integer exists islands.id
  - `house_name` string max 255
  - `floor` string max 50
  - `email` email max 255 unique users.email ignore current user
- Success `200` (`UserResource`): same shape as `GET /me`
- Errors:
  - `401` unauthenticated
  - `422` validation errors for bad or duplicate values

## POST /api/v1/me/contact-numbers

- Request body (`application/json`):
  - `contact_numbers` array required min 1 max 3
  - `contact_numbers.*` string required max 30
- Success `200`:

```json
{
  "message": "Contact numbers updated successfully.",
  "user": {
    "id": 1,
    "name": "...",
    "contact_numbers": [
      {
        "id": 3,
        "number": "9607xxxxxx",
        "position": 1
      }
    ]
  }
}
```

- Note: `user` is returned as a nested JSON resource object.
- Errors:
  - `401` unauthenticated
  - `422` validation errors

## Pagination

No endpoints in this file are paginated.

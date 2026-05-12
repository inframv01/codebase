# Notifications API Contract

All endpoints in this file require:
- `auth:sanctum`
- `throttle:api`

Notification resource item (`DatabaseNotificationResource`):

```json
{
  "id": "db-notification-id",
  "type": "App\\Notifications\\DeliveryStageAdvanced",
  "data": {},
  "read_at": null,
  "created_at": "2026-05-12T10:00:00+00:00"
}
```

## GET /api/v1/notifications

- Query:
  - `per_page` optional integer, default `15`, clamped to `1..100`
- Success `200` paginated collection:

```json
{
  "data": [
    {
      "id": "db-notification-id",
      "type": "App\\Notifications\\DeliveryStageAdvanced",
      "data": {},
      "read_at": null,
      "created_at": "2026-05-12T10:00:00+00:00"
    }
  ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "path": "...",
    "per_page": 15,
    "to": 15,
    "total": 45
  }
}
```

- Errors:
  - `401` unauthenticated

## POST /api/v1/notifications/{notificationId}/read

- Path params:
  - `notificationId` string database notification primary key
- Request body: none
- Success `200`:

```json
{
  "message": "Notification marked as read."
}
```

- Errors:
  - `404` when notification is not found for current user

## POST /api/v1/notifications/read-all

- Request body: none
- Success `200`:

```json
{
  "message": "All notifications marked as read."
}
```

## Pagination

Only `GET /api/v1/notifications` is paginated.

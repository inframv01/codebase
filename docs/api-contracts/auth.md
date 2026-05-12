# Auth API Contract

All endpoints in this file are under `/api/v1/auth` and are public (no bearer token required).

## POST /api/v1/auth/register

- Middleware: `throttle:auth`
- Request body (`application/json`):
  - `name` string required max 255
  - `id_card_number` string required max 50 unique users.id_card_number
  - `atoll_id` integer required min 1
  - `island_id` integer required min 1
  - `house_name` string required max 255
  - `floor` string required max 50
  - `contact_numbers` array required min 1 max 3
  - `contact_numbers.*` string required max 30
  - `email` string email required max 255 unique users.email
  - `password` string required min 8
- Success:
  - `201`

```json
{
  "message": "Registration successful. Verify the OTP sent to your email address."
}
```

- Errors:
  - `422` validation errors (including duplicate email/id card)
  - `422` from OTP issuance throttle: `errors.email[0] = "Too many OTP requests. Please try again later."`

## POST /api/v1/auth/login

- Middleware: `throttle:auth`
- Request body (`application/json`):
  - `email` email required
  - `password` string required
- Success:
  - `200`

```json
{
  "message": "Login successful.",
  "token": "<sanctum-token>",
  "user": {
    "id": 1,
    "name": "...",
    "id_card_number": "...",
    "atoll_id": 1,
    "island_id": 2,
    "house_name": "...",
    "floor": "...",
    "email": "...",
    "google_id": null,
    "role": "customer",
    "email_verified_at": "2026-05-12T10:00:00+00:00",
    "created_at": "...",
    "updated_at": "...",
    "contact_numbers": [
      {
        "id": 11,
        "user_id": 1,
        "number": "9607xxxxxx",
        "position": 1,
        "created_at": "...",
        "updated_at": "..."
      }
    ]
  }
}
```

- Errors:
  - `422` invalid credentials: `errors.email[0] = "The provided credentials are incorrect."`
  - `422` unverified email: `errors.email[0] = "Verify your email address before signing in."`

## GET /api/v1/auth/google/redirect

- Middleware: `throttle:auth`
- Request: none
- Success:
  - `200`

```json
{
  "url": "https://accounts.google.com/o/oauth2/..."
}
```

- Errors:
  - `500` provider/config/runtime errors (global `{ "message": "..." }`)

## GET /api/v1/auth/google/callback

- Middleware: `throttle:auth`
- Request: OAuth callback query params from Google provider
- Success:
  - `200`

```json
{
  "message": "Google authentication successful.",
  "token": "<sanctum-token>",
  "user": {
    "id": 1,
    "name": "...",
    "email": "...",
    "role": "customer",
    "contact_numbers": []
  }
}
```

- Errors:
  - `500` callback/provider/runtime errors

## POST /api/v1/auth/forgot-password

- Middleware: `throttle:auth`
- Request body (`application/json`):
  - `email` email required
- Success:
  - `200`

```json
{
  "message": "We have emailed your password reset link."
}
```

- Errors:
  - `422` password broker failures under `errors.email`

## POST /api/v1/auth/reset-password

- Middleware: `throttle:auth`
- Request body (`application/json`):
  - `token` string required
  - `email` email required
  - `password` string required min 8
  - `password_confirmation` string required by `confirmed` rule
- Success:
  - `200`

```json
{
  "message": "Your password has been reset."
}
```

- Errors:
  - `422` invalid token/email/password policy failures

## POST /api/v1/auth/otp/resend

- Middleware: `throttle:auth` + `throttle:otp`
- Request body (`application/json`):
  - `email` email required
- Success:
  - `202`

```json
{
  "message": "A new OTP has been sent to your email address."
}
```

- Errors:
  - `422` no account: `errors.email[0] = "No account was found for this email address."`
  - `422` OTP hourly cap: `errors.email[0] = "Too many OTP requests. Please try again later."`

## POST /api/v1/auth/otp/verify

- Middleware: `throttle:auth` + `throttle:otp`
- Request body (`application/json`):
  - `email` email required
  - `code` required exactly 6 digits
- Success:
  - `200`

```json
{
  "message": "Email verified successfully.",
  "token": "<sanctum-token>",
  "user": {
    "id": 1,
    "name": "...",
    "email": "...",
    "role": "customer",
    "contact_numbers": []
  }
}
```

- Errors:
  - `422` no account: `errors.email[0] = "No account was found for this email address."`
  - `422` no valid otp: `errors.code[0] = "No valid OTP was found for this email address."`
  - `422` invalid otp: `errors.code[0] = "The provided OTP is invalid."`

## Pagination

No endpoints in this file are paginated.

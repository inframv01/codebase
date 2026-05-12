# Maldiv Delivery Backend API

Laravel 13 JSON API for the Maldiv Delivery platform.

## Stack

- PHP 8.3+
- Laravel 13
- PostgreSQL
- Sanctum personal access tokens
- Socialite (Google)
- Pest v4

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

## Required Environment

```env
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dbmaldelivery
DB_USERNAME=postgres
DB_PASSWORD=secret

MAIL_MAILER=log

FILESYSTEM_DISK=local
UPLOADS_DISK_DRIVER=local
UPLOADS_DISK_VISIBILITY=private
UPLOADS_BUCKET=
UPLOADS_URL=
UPLOADS_ENDPOINT=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=${APP_URL}/api/v1/auth/google/callback
```

For production, point the `uploads` disk to S3-compatible storage by setting `UPLOADS_DISK_DRIVER=s3` and the corresponding bucket credentials.

## Run

```bash
composer run dev
```

API base URL:

```text
/api/v1
```

## Main API Areas

- `POST /auth/register`, `POST /auth/otp/verify`, `POST /auth/login`
- `GET|PATCH /me`, `POST /me/contact-numbers`
- `GET /lookups/*`, `POST /quotes/preview`
- `GET|POST /addresses`
- `GET|POST /delivery-requests`, `POST /delivery-requests/{uuid}/payments`
- `GET /notifications`
- `GET|POST /operator/*` for operator workflows

## Example Flow

Register:

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
	-H "Accept: application/json" \
	-H "Content-Type: application/json" \
	-d '{
		"name": "Ahmed Ali",
		"id_card_number": "A1234567",
		"atoll_id": 8,
		"island_id": 10,
		"house_name": "Beach House",
		"floor": "2",
		"contact_numbers": ["+9607000001", "+9607000002"],
		"email": "ahmed@example.com",
		"password": "password123"
	}'
```

Verify OTP:

```bash
curl -X POST http://localhost:8000/api/v1/auth/otp/verify \
	-H "Accept: application/json" \
	-H "Content-Type: application/json" \
	-d '{
		"email": "ahmed@example.com",
		"code": "123456"
	}'
```

Create a delivery request:

```bash
curl -X POST http://localhost:8000/api/v1/delivery-requests \
	-H "Accept: application/json" \
	-H "Authorization: Bearer YOUR_TOKEN" \
	-F type=male_address \
	-F destination_island_id=10 \
	-F weight_kg=2.5 \
	-F 'address={"line_1":"Ameenee Magu","city":"Male"}' \
	-F contact_name="Pickup Contact" \
	-F contact_phone="+9607000003"
```

Upload a bank transfer slip:

```bash
curl -X POST http://localhost:8000/api/v1/delivery-requests/REQUEST_UUID/payments \
	-H "Accept: application/json" \
	-H "Authorization: Bearer YOUR_TOKEN" \
	-F amount_cents=3000 \
	-F slip=@/path/to/slip.png
```

## Tests and Formatting

```bash
php artisan test --compact
./vendor/bin/pint --test
```

## Seeded Lookup Data

Seeders currently provide:

- all 20 Maldives atolls
- a representative set of real islands, including Malé and Hulhumalé
- three sample boat providers and island groups
- sample pricing rules for fixed-cost and inspection-required flows

Expand the seed data before production rollout if you need full island coverage and operational routes.

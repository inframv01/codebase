# 6. Database Schema Context

## 6.1 Existing Tables

| Table | Purpose | Key Columns | Relationships | Migration File |
|---|---|---|---|---|
| users | Core account records | id, email, password(nullable), role, google_id, id_card_number, atoll_id, island_id | hasMany contact numbers, delivery requests, saved addresses | backend/database/migrations/0001_01_01_000000_create_users_table.php + 2026_05_11_104100_expand_users_for_delivery_platform.php |
| password_reset_tokens | Password reset broker store | email(primary), token, created_at | by email | backend/database/migrations/0001_01_01_000000_create_users_table.php |
| sessions | Session storage | id(string), user_id, payload | optional user_id FK-like field | backend/database/migrations/0001_01_01_000000_create_users_table.php |
| personal_access_tokens | Sanctum tokens | id, tokenable_type/id, token, abilities, expires_at | morph tokenable | backend/database/migrations/2026_05_11_103112_create_personal_access_tokens_table.php |
| user_contact_numbers | User phone list | id, user_id, number, position | belongsTo user; unique(user_id, position) | backend/database/migrations/2026_05_11_104200_create_user_contact_numbers_table.php |
| email_otps | OTP verification state | id, email, code_hash, expires_at, consumed_at, attempts | email-based lookup | backend/database/migrations/2026_05_11_104300_create_email_otps_table.php |
| atolls | Geographic lookup | id, code(unique), name | hasMany islands | backend/database/migrations/2026_05_11_120000_create_atolls_table.php |
| islands | Geographic lookup | id, atoll_id, name(unique per atoll) | belongsTo atoll; belongsToMany island_groups | backend/database/migrations/2026_05_11_120100_create_islands_table.php |
| island_groups | Grouping islands | id, name(unique) | belongsToMany islands; belongsToMany boats | backend/database/migrations/2026_05_11_120200_create_island_groups_table.php |
| island_group_island | Pivot island-group | id, island_group_id, island_id | pivot table | backend/database/migrations/2026_05_11_120200_create_island_groups_table.php |
| transport_providers | Carrier/operator lookup | id, name(unique), type, active | hasMany boats | backend/database/migrations/2026_05_11_120300_create_transport_providers_table.php |
| boats | Boat assets | id, transport_provider_id, name, capacity_kg, active | belongsTo transport_provider; many schedules; many island_groups | backend/database/migrations/2026_05_11_120400_create_boats_table.php |
| boat_island_group | Pivot boat-group | id, boat_id, island_group_id | pivot table | backend/database/migrations/2026_05_11_120400_create_boats_table.php |
| boat_schedules | Sailing schedule | id, boat_id, origin_island_id, destination_island_id, departs_at, arrives_at, status | belongsTo boat + islands | backend/database/migrations/2026_05_11_120500_create_boat_schedules_table.php |
| pricing_rules | Quote rules | id, scope_type, scope_id, service_type, fixed_cost_cents, variable_rate_cents_per_kg, min_charge_cents, requires_inspection, active | polymorphic-like scope by type/id | backend/database/migrations/2026_05_11_120600_create_pricing_rules_table.php |
| notifications | Laravel database notifications | id(uuid), notifiable_type/id, data, read_at | morph notifiable | backend/database/migrations/2026_05_11_130000_create_notifications_table.php |
| saved_addresses | Customer address book | id, user_id, label, purpose, address(jsonb), contact fields, is_default | belongsTo user | backend/database/migrations/2026_05_11_130100_create_saved_addresses_table.php |
| delivery_requests | Core delivery workflow | id, uuid, user_id, type, destination_island_id, provider/schedule refs, status/current_stage, cost fields, accepted_by_operator_id | belongsTo user/island/provider/schedule/operator; hasOne details; hasMany stage events/payments | backend/database/migrations/2026_05_11_130200_create_delivery_requests_table.php |
| delivery_post_office_details | Type-specific details | id, delivery_request_id(unique), tracking_number, screenshot_path | belongsTo delivery_request | backend/database/migrations/2026_05_11_130300_create_delivery_request_details_tables.php |
| delivery_male_address_details | Type-specific details | id, delivery_request_id(unique), address(jsonb), contact fields | belongsTo delivery_request | backend/database/migrations/2026_05_11_130300_create_delivery_request_details_tables.php |
| delivery_shop_details | Type-specific details | id, delivery_request_id(unique), shop_address(jsonb), quote_path/items(jsonb) | belongsTo delivery_request; DB check quote xor items | backend/database/migrations/2026_05_11_130300_create_delivery_request_details_tables.php |
| delivery_stage_events | Stage timeline | id, delivery_request_id, stage, occurred_at, actor_user_id | belongsTo delivery_request + optional actor user | backend/database/migrations/2026_05_11_130400_create_delivery_stage_events_table.php |
| payments | Payment slips and verification | id, uuid, delivery_request_id, amount_cents, slip_path, status, verifier refs | belongsTo delivery_request + verifier user | backend/database/migrations/2026_05_11_130500_create_payments_table.php |
| cache/cache_locks/jobs/job_batches/failed_jobs | Framework infra | standard Laravel infra columns | framework | backend/database/migrations/0001_01_01_000001_create_cache_table.php + 0001_01_01_000002_create_jobs_table.php |

## 6.2 ID Strategy
- Primary keys are mixed but mostly integer auto-increment ids.
- UUID usage:
  - delivery_requests.uuid unique business key and route key.
  - payments.uuid unique business key and route key.
  - notifications.id UUID primary key (Laravel notifications standard).
  - failed_jobs.uuid unique but table PK is integer id.
- Foreign keys largely reference integer ids.
- Conclusion: mixed strategy (int PK baseline + selective UUID external identifiers).

Evidence:
- backend/database/migrations/2026_05_11_130200_create_delivery_requests_table.php
- backend/database/migrations/2026_05_11_130500_create_payments_table.php
- backend/app/Models/DeliveryRequest.php:59-62
- backend/app/Models/Payment.php:43-46

## 6.3 Naming Conventions
- DB/table/column naming: snake_case consistently.
- JSON/API fields: snake_case consistently through resources and requests.
- Enums:
  - PHP enum case names use PascalCase.
  - Stored/transmitted enum values use snake_case strings.
- No camelCase API contracts in backend resources.

Evidence:
- backend/app/Http/Resources/*.php
- backend/app/Enums/*.php
- backend/app/Http/Requests/*.php

# 7. API Context

## API Route Files
- Primary API routes: backend/routes/api.php
- API error renderer: backend/bootstrap/app.php

## Endpoint Table

| Method | Path | Controller/Action | Auth Required | Tenant Required | Notes |
|---|---|---|---|---|---|
| POST | /api/v1/auth/register | RegisterController::__invoke | No | No | throttle:auth |
| POST | /api/v1/auth/login | LoginController::__invoke | No | No | verified email required |
| GET | /api/v1/auth/google/redirect | GoogleAuthController@redirect | No | No | returns provider URL |
| GET | /api/v1/auth/google/callback | GoogleAuthController@callback | No | No | issues token |
| POST | /api/v1/auth/forgot-password | PasswordResetController@forgot | No | No | password broker |
| POST | /api/v1/auth/reset-password | PasswordResetController@reset | No | No | password broker |
| POST | /api/v1/auth/otp/resend | OtpController@resend | No | No | throttle:otp |
| POST | /api/v1/auth/otp/verify | OtpController@verify | No | No | issues token |
| GET | /api/v1/health | closure | No | No | health probe |
| GET | /api/v1/me | MeController@show | Yes | No | auth:sanctum |
| PATCH | /api/v1/me | MeController@update | Yes | No | auth:sanctum |
| POST | /api/v1/me/contact-numbers | MeController@replaceContactNumbers | Yes | No | auth:sanctum |
| GET | /api/v1/lookups/atolls | AtollLookupController | Yes | No | auth:sanctum |
| GET | /api/v1/lookups/islands | IslandLookupController | Yes | No | optional atoll filter |
| GET | /api/v1/lookups/transport-providers | TransportProviderLookupController | Yes | No | optional island filter |
| GET | /api/v1/lookups/boats/schedules | BoatScheduleLookupController | Yes | No | filters island/date range |
| POST | /api/v1/quotes/preview | QuotePreviewController | Yes | No | pricing preview |
| GET | /api/v1/addresses | SavedAddressController@index | Yes | No | policy viewAny |
| POST | /api/v1/addresses | SavedAddressController@store | Yes | No | policy create |
| GET | /api/v1/addresses/{id} | SavedAddressController@show | Yes | No | policy view |
| PATCH/PUT | /api/v1/addresses/{id} | SavedAddressController@update | Yes | No | policy update |
| DELETE | /api/v1/addresses/{id} | SavedAddressController@destroy | Yes | No | policy delete, 204 |
| GET | /api/v1/delivery-requests | Customer DeliveryRequestController@index | Yes | No | paginated |
| POST | /api/v1/delivery-requests | Customer DeliveryRequestController@store | Yes | No | multipart supported |
| GET | /api/v1/delivery-requests/{uuid} | Customer DeliveryRequestController@show | Yes | No | policy viewCustomer |
| POST | /api/v1/delivery-requests/{uuid}/cancel | Customer DeliveryRequestController@cancel | Yes | No | policy cancel |
| POST | /api/v1/delivery-requests/{uuid}/payments | Customer PaymentController@store | Yes | No | policy uploadPayment |
| GET | /api/v1/notifications | NotificationController@index | Yes | No | paginated |
| POST | /api/v1/notifications/{id}/read | NotificationController@read | Yes | No | ownership by current user notifications |
| POST | /api/v1/notifications/read-all | NotificationController@readAll | Yes | No | unread->markAsRead |
| ANY CRUD | /api/v1/operator/{resources} | Operator resource controllers | Yes | No | requires role + abilities |
| GET | /api/v1/operator/delivery-requests | Operator DeliveryRequestController@index | Yes | No | plus policy viewAnyOperator |
| POST | /api/v1/operator/delivery-requests/{uuid}/quote | Operator DeliveryRequestController@quote | Yes | No | policy quote |
| POST | /api/v1/operator/delivery-requests/{uuid}/accept | Operator DeliveryRequestController@accept | Yes | No | policy accept |
| POST | /api/v1/operator/delivery-requests/{uuid}/stage | Operator DeliveryRequestController@stage | Yes | No | policy stage |
| POST | /api/v1/operator/delivery-requests/{uuid}/payments/{paymentUuid}/verify | Operator DeliveryRequestController@verifyPayment | Yes | No | policy verifyPayment |
| POST | /api/v1/operator/delivery-requests/{uuid}/payments/{paymentUuid}/reject | Operator DeliveryRequestController@rejectPayment | Yes | No | policy rejectPayment |

## Request Validation Patterns
- FormRequest classes used across all major endpoints.
- Complex conditional validation via after() hook in StoreDeliveryRequestRequest.
- Files validated with mime and size constraints.

Evidence:
- backend/app/Http/Requests/**/*.php

## Response Resource Patterns
- Unwrapped JsonResource output via JsonResource::withoutWrapping.
- Collections return plain arrays unless paginated.
- Paginated responses use Laravel data/links/meta shape.

Evidence:
- backend/app/Providers/AppServiceProvider.php:35
- backend/app/Http/Resources/*.php
- backend/app/Http/Controllers/Api/Customer/DeliveryRequestController.php:28
- backend/app/Http/Controllers/Api/NotificationController.php:18

## Error Response Conventions
- API exceptions rendered as JSON with status/code/errors fields.
- Validation, authentication, authorization explicitly mapped.

Evidence:
- backend/bootstrap/app.php:34-74

## Rate Limiting
- auth: 5/min by IP
- otp: 5/hour by email (or IP fallback)
- api: 60/min by user id or IP

Evidence:
- backend/app/Providers/AppServiceProvider.php:40-42

# 4. Authentication And Authorization Context

## Login / Registration Methods
- Email-password registration implemented:
  - POST /api/v1/auth/register
  - backend/routes/api.php:30
  - backend/app/Http/Controllers/Api/Auth/RegisterController.php
- OTP verification required after registration before login success:
  - POST /api/v1/auth/otp/verify
  - backend/routes/api.php:39
  - backend/app/Http/Controllers/Api/Auth/OtpController.php
- Email-password login implemented:
  - POST /api/v1/auth/login
  - backend/app/Http/Controllers/Api/Auth/LoginController.php
- Google social login implemented:
  - GET /api/v1/auth/google/redirect
  - GET /api/v1/auth/google/callback
  - backend/app/Http/Controllers/Api/Auth/GoogleAuthController.php
- Password reset implemented:
  - POST /api/v1/auth/forgot-password
  - POST /api/v1/auth/reset-password
  - backend/app/Http/Controllers/Api/Auth/PasswordResetController.php

## Token Issuing Behavior
- Sanctum personal access tokens issued on:
  - login
  - otp verify
  - google callback
- Token abilities:
  - operator/admin => [operator]
  - customer => [customer]
- Evidence:
  - backend/app/Http/Controllers/Api/Auth/LoginController.php:41-46
  - backend/app/Http/Controllers/Api/Auth/OtpController.php:61-66
  - backend/app/Http/Controllers/Api/Auth/GoogleAuthController.php:50

## Auth Middleware Usage
- Protected API routes use auth:sanctum + throttle:api.
  - backend/routes/api.php:45
- Operator routes require both:
  - role:operator,admin
  - abilities:operator
  - backend/routes/api.php:65
- role middleware checks enum value directly.
  - backend/app/Http/Middleware/EnsureUserHasRole.php

## Email Verification / OTP Behavior
- login denies users with email_verified_at null.
  - backend/app/Http/Controllers/Api/Auth/LoginController.php:26-29
- OTP codes:
  - hashed before storage
  - expire in 10 minutes
  - max 5 verification attempts per code record
  - issue capped at 5 per hour per email
- Evidence:
  - backend/app/Services/OtpService.php:14-37, 40-73
  - backend/database/migrations/2026_05_11_104300_create_email_otps_table.php

## RBAC Model
- Role values are enum-backed:
  - customer, operator, admin
  - backend/app/Enums/UserRole.php
- Coarse RBAC via middleware on route groups.
- Fine RBAC via policies for ownership and operator constraints.
  - backend/app/Policies/DeliveryRequestPolicy.php
  - backend/app/Policies/PaymentPolicy.php
  - backend/app/Policies/SavedAddressPolicy.php
- Policy registration explicit in AppServiceProvider.
  - backend/app/Providers/AppServiceProvider.php:36-38

## Permission Refresh / Stale Token Risks
- Potential risk: role changes after token issuance may desync with ability checks on existing tokens.
  - ability is stored at token creation time.
  - role middleware checks current user role from DB.
  - Combined check mitigates some cases but can still produce inconsistent UX until token refresh.
- No explicit token revocation endpoint detected.
- Sanctum expiration is null by default (non-expiring token unless overridden).
  - backend/config/sanctum.php:53

# 5. Multi-Tenancy Context

## Package / Infrastructure
- No tenancy package detected in backend/composer.json.
- No tenant model, tenant middleware, or tenant-scoped global scopes found in backend app code.

## Tenant Data Model
- No tenant_id fields in inspected core models/migrations.
- No user-to-tenant relation implemented.
- No active tenant selection/switching flow implemented.

## Database Strategy Classification
- Current strategy: single database, non-tenant-aware shared tables.
- Evidence:
  - backend/database/migrations/*.php (no tenant table or tenant key columns)
  - backend/config/database.php (single default connection)

## Tenant Isolation Enforcement
- Not implemented (no tenant context resolver/middleware/policies).

## Tenant Leakage Risks
- If tenancy is added later, many queries currently rely on global visibility assumptions.
- Operator delivery listing currently scopes by operator assignment logic, not tenant boundary.
  - backend/app/Http/Controllers/Api/Operator/DeliveryRequestController.php:36-49

# 9. Admin Panel Context

## Framework Detection
- No Filament/Nova/Backpack admin panel package in backend/composer.json.
- No panel provider or admin resource tree detected in app code.

## Current Admin Pattern
- Admin is role value inside the same user model.
- Admin accesses operator endpoints through role + ability middleware and policy checks.
  - backend/routes/api.php:65
  - backend/app/Enums/UserRole.php

## Admin Route / Guard
- No separate admin path/guard/panel.
- Admin uses same API auth (Sanctum bearer token) and same operator route tree.

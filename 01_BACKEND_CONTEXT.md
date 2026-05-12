# Backend Context

## Backend Directory Map
- Entry routes: backend/routes/api.php, backend/routes/web.php, backend/routes/console.php
- Bootstrapping and exception renderer: backend/bootstrap/app.php
- Providers and rate limits: backend/app/Providers/AppServiceProvider.php
- Domain models: backend/app/Models
- API controllers: backend/app/Http/Controllers/Api
- Validation requests: backend/app/Http/Requests
- API resources: backend/app/Http/Resources
- Policies: backend/app/Policies
- Services: backend/app/Services
- Jobs/notifications: backend/app/Jobs, backend/app/Notifications

## Main Implemented Backend Features
- Registration with OTP verification flow.
- Login with verified-email gate.
- Google OAuth login/callback.
- Password reset via broker token flow.
- Customer profile and contact number management.
- Lookup APIs (atolls, islands, transport providers, boat schedules, quote preview).
- Customer delivery request lifecycle with polymorphic request detail records.
- Payment slip upload + operator verify/reject workflow.
- Operator CRUD for lookup resources and pricing rules.
- Operator delivery queue with assignment constraints.
- In-app + mail notifications for key delivery/payment events.

Evidence:
- backend/routes/api.php
- backend/app/Http/Controllers/Api/**
- backend/app/Services/**
- backend/app/Notifications/**

## Service/Action Pattern Used
- Delivery creation orchestration in service layer:
  - Pricing resolution + record creation + detail persistence + stage events + job dispatch.
  - File: backend/app/Services/DeliveryRequestService.php
- Stage transition rules centralized in service:
  - Valid stage checking, monotonic progression, status mapping.
  - File: backend/app/Services/DeliveryStageService.php
- Quote computation centralized in service:
  - Scope-specific pricing rule selection and money calculations.
  - File: backend/app/Services/PricingResolver.php
- OTP issuance/verification centralized in service:
  - Rate cap, hashing, attempt limits, consume logic.
  - File: backend/app/Services/OtpService.php

## Middleware / Authorization Composition
- Route-level middleware gates:
  - auth:sanctum + throttle:api for protected endpoints.
  - role:operator,admin + abilities:operator for operator subtree.
  - File: backend/routes/api.php
- Middleware alias registration:
  - abilities => Laravel Sanctum CheckAbilities
  - role => EnsureUserHasRole custom middleware
  - File: backend/bootstrap/app.php:20-24
- Additional fine-grained authorization via policies/Gate::authorize in controllers.
  - Files: backend/app/Policies/*.php, backend/app/Http/Controllers/Api/Customer/DeliveryRequestController.php, backend/app/Http/Controllers/Api/Operator/DeliveryRequestController.php

## Response And Error Conventions
- JsonResource wrapping disabled globally:
  - JsonResource::withoutWrapping() in backend/app/Providers/AppServiceProvider.php:35
- API validation/auth/authz/http/server errors normalized to JSON envelope:
  - { message, status, code, errors }
  - File: backend/bootstrap/app.php:34-74
- Controller action responses:
  - Resource returns for entities.
  - Explicit message payloads for actions (cancel, accept, verify, etc.).
  - 204 for deletions.

## Model/ORM Conventions
- Eloquent attributes for mass assignment and hidden fields:
  - #[Fillable([...])] and #[Hidden([...])] used in models.
  - Example: backend/app/Models/User.php
- Enums for role, status, stage cast at model level.
  - backend/app/Enums/UserRole.php
  - backend/app/Enums/DeliveryStatus.php
  - backend/app/Enums/DeliveryStage.php
- Money cast uses integer round-trip convention.
  - backend/app/Casts/MoneyCents.php
- UUIDv7 route keys for selected models:
  - DeliveryRequest and Payment expose uuid for route binding.
  - backend/app/Models/DeliveryRequest.php:59-62
  - backend/app/Models/Payment.php:43-46
- Lazy loading prevention in non-production.
  - backend/app/Providers/AppServiceProvider.php:34

## Queue/Notification Pattern
- Notifications largely implement ShouldQueue and deliver via database+mail channels.
  - backend/app/Notifications/*.php
- New delivery request fan-out to operator/admin users via queued job.
  - backend/app/Jobs/NotifyOperatorsOfNewDeliveryRequest.php

## Code Conventions And Patterns (Backend)
- No global declare(strict_types=1) pattern observed.
- Controllers are thin-to-moderate; orchestration delegated to services for workflow-heavy paths.
- FormRequest classes consistently authorize() => true; authorization is usually route middleware + policies.
- Validation rules are snake_case field names aligned to API payloads.
- Resources output snake_case keys and ISO8601 date strings where applicable.
- Database IDs mostly integer primary keys; UUID used as alternate business key in select tables.

## Gaps / Fragile Spots To Keep In Mind
- Authorization is layered but split across route middleware and policy checks; future changes must preserve both.
- Pricing and stage logic are centralized and should not be duplicated in controllers.
- Filesystem upload paths are persisted as storage keys; frontend should not assume public URL without explicit resolver.
- Some docs claim different error/response behavior than current bootstrap renderer (see 06_AGENT_HANDOFF_CONTEXT.md).

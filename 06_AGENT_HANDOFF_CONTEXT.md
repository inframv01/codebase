# 12. Code Conventions And Patterns

## Backend Conventions
- Controllers under backend/app/Http/Controllers/Api grouped by domain.
- Validation via FormRequest classes in backend/app/Http/Requests.
- Authorization via route middleware + policies + Gate::authorize.
- API transformations in JsonResource classes (without wrapping globally).
- Service classes used for complex business flows (pricing, stage transitions, delivery creation, OTP).
- Model fillable/hidden expressed with PHP attributes.
- Enum values and API fields use snake_case.

Evidence:
- backend/app/Http/Controllers/Api/**
- backend/app/Http/Requests/**
- backend/app/Policies/**
- backend/app/Http/Resources/**
- backend/app/Services/**
- backend/app/Models/**

## Frontend Conventions
- One route-level page per file under frontend/src/pages.
- API access centralized in frontend/src/api modules.
- Auth context in frontend/src/auth with localStorage persistence.
- React Query for fetch/mutation cache lifecycle.
- Zod + React Hook Form for form validation.
- Tailwind utility-based component styling.

Evidence:
- frontend/src/pages/**
- frontend/src/api/**
- frontend/src/auth/**
- frontend/src/components/**

# 13. Documentation Context

| Document | Purpose | Still Accurate? | Notes |
|---|---|---|---|
| AGENTS.md | repository-level architecture/conventions | Mostly yes | Good high-level orientation and commands. |
| backend/README.md | backend setup and API summary | Mostly yes | Useful; endpoint examples may age over time. |
| docs/api-contracts/README.md | API contract index and conventions | Partially | Mentions global non-validation error shape as message-only, but bootstrap currently returns message+status+code+errors. |
| docs/api-contracts/auth.md | auth endpoint contracts | Mostly yes | Keep synced with actual user payload shape and resource fields. |
| docs/api-contracts/me-profile.md | me/profile endpoints | Mostly yes | Health endpoint listed though grouped with profile doc. |
| docs/api-contracts/addresses.md | address endpoints | Mostly yes | Ownership notes are conceptually correct (policy-driven). |
| docs/api-contracts/delivery-requests.md | delivery + payment workflows | Mostly yes | good conditional validation detail. |
| docs/api-contracts/operator.md | operator endpoint contracts | Partially | Some examples imply wrapped data object for update responses; backend uses unwrapped resources globally. |
| docs/api-contracts/lookups-and-quotes.md | lookup/quote endpoints | Not fully verified here | Read alongside controllers before edits. |
| docs/api-contracts/notifications.md | notifications endpoints | Mostly yes | Pagination expectation aligns with controller. |
| frontend/README.md | frontend setup | No (project-specific) | Still default Vite template content; weak project guidance. |

## Important Docs To Read First
1. AGENTS.md
2. backend/routes/api.php
3. backend/bootstrap/app.php
4. backend/app/Providers/AppServiceProvider.php
5. docs/api-contracts/README.md (then domain docs)

## Known Doc-vs-Code Contradictions
- Error envelope mismatch:
  - docs/api-contracts/README.md suggests message-only for non-validation API exceptions.
  - backend/bootstrap/app.php currently returns message/status/code/errors.
- Resource wrapping mismatch examples:
  - some docs/operator examples show wrapped data for updates.
  - backend/app/Providers/AppServiceProvider.php disables wrapping globally.

# 16. Source-of-Truth Files For Future Agents (Max 25)

| Priority | File / Directory | Why It Matters |
|---|---|---|
| 1 | backend/routes/api.php | Full endpoint map, middleware layering, versioning. |
| 2 | backend/bootstrap/app.php | API exception contract and middleware aliases. |
| 3 | backend/app/Providers/AppServiceProvider.php | policies, rate limits, resource wrapping, lazy-loading policy. |
| 4 | backend/app/Http/Controllers/Api/Auth | auth flows and token issuance. |
| 5 | backend/app/Http/Controllers/Api/Customer | customer workflow behavior. |
| 6 | backend/app/Http/Controllers/Api/Operator | operator workflow and RBAC-sensitive actions. |
| 7 | backend/app/Http/Requests | request validation rules and conditional constraints. |
| 8 | backend/app/Policies | ownership and operator authorization logic. |
| 9 | backend/app/Services/DeliveryRequestService.php | delivery creation orchestration. |
| 10 | backend/app/Services/DeliveryStageService.php | stage progression invariants and status mapping. |
| 11 | backend/app/Services/PricingResolver.php | quote computation source of truth. |
| 12 | backend/app/Services/OtpService.php | OTP security and lifecycle rules. |
| 13 | backend/app/Models/DeliveryRequest.php | UUID route key + relationships + casts. |
| 14 | backend/app/Models/Payment.php | UUID route key + payment linkage. |
| 15 | backend/app/Enums | role/status/stage canonical values. |
| 16 | backend/database/migrations | schema, ids, FK strategy, data constraints. |
| 17 | backend/app/Http/Resources | API response shapes. |
| 18 | backend/config/sanctum.php | token expiration and sanctum behavior. |
| 19 | backend/config/filesystems.php | upload storage behavior and privacy defaults. |
| 20 | backend/tests/Feature | behavior regression safety net and expected outcomes. |
| 21 | frontend/src/App.tsx | frontend route map and guard topology. |
| 22 | frontend/src/lib/api.ts | auth header behavior, base URL, 401 handling. |
| 23 | frontend/src/auth/AuthContext.tsx | auth state model and operator role logic. |
| 24 | frontend/src/api | frontend backend-contract assumptions by endpoint. |
| 25 | frontend/src/types.ts | frontend domain types and potential mismatch hotspots. |

# 17. Agent Handoff Summary

## Paste This Into ChatGPT

Project purpose:
- Maldiv Delivery platform with separate Laravel backend API and React frontend SPA.

Stack:
- Backend: PHP 8.3+, Laravel 13, Sanctum, Socialite, Pest.
- Frontend: React 19, TypeScript strict, Vite, React Query, Axios, React Router, Tailwind v4.
- DB default: PostgreSQL.

Implemented features:
- Auth: register/login, OTP verify/resend, Google OAuth callback, forgot/reset password.
- Customer: profile, contact numbers, address CRUD, delivery request create/list/show/cancel, payment slip upload, notifications.
- Operator/admin: CRUD for lookup resources (atolls/islands/groups/providers/boats/schedules/pricing), delivery queue, quote/accept/stage, payment verify/reject.

Auth model:
- Bearer Sanctum personal access tokens.
- Token abilities: customer vs operator.
- Route middleware and policy checks combined.

Tenancy model:
- No tenancy package or tenant model currently.
- Single shared schema; no tenant boundary enforcement.

RBAC model:
- Custom role enum (customer/operator/admin) + custom role middleware.
- Additional policy checks on delivery/payment/address ownership and operator constraints.

Database ID strategy:
- Mostly integer PKs.
- UUID business keys for delivery_requests and payments (used for route binding).
- Notifications table uses UUID id.

API conventions:
- Base path /api/v1.
- FormRequest validation + JsonResource outputs (unwrapped).
- API errors rendered with message/status/code/errors.
- Rate limiting: auth 5/min, otp 5/hour, api 60/min.

Frontend status:
- Route-guarded SPA with auth context/localStorage token.
- Domain API modules exist for all backend areas.
- Several customer/operator screens are implemented.

Admin panel status:
- No dedicated Filament/Nova admin panel.
- Admin role uses same operator API surfaces.

Testing status:
- Pest feature coverage exists for auth, lookups, customer flows, operator flows, delivery workflow.
- No frontend automated tests detected.
- No tenancy tests (tenancy absent).

Security constraints:
- OTP hashed + expiry + attempt caps.
- Passwords hashed via model cast.
- Uploads default private on uploads disk.
- Sanctum token expiration is null by default (non-expiring unless configured).
- No explicit token revoke/logout endpoint detected.

Files future agents must preserve carefully:
- backend/routes/api.php
- backend/bootstrap/app.php
- backend/app/Providers/AppServiceProvider.php
- backend/app/Services/DeliveryRequestService.php
- backend/app/Services/DeliveryStageService.php
- backend/app/Services/PricingResolver.php
- backend/app/Policies/*.php
- backend/app/Http/Requests/*.php
- backend/app/Http/Resources/*.php
- frontend/src/lib/api.ts
- frontend/src/api/*.ts
- frontend/src/types.ts

Known risks:
- Frontend expects array for customer delivery list while backend returns paginated payload.
- Frontend status union includes payment_uploaded while backend uses payment_review.
- API docs have some drift vs current bootstrap error envelope and wrapping behavior.
- Workspace snapshot has no .git metadata, so vcs state could not be audited.

Assumptions:
- No hidden external services beyond env placeholders.
- No unseen tenancy/admin implementation outside inspected directories.
- Findings based on current filesystem snapshot only.

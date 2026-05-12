# 1. Executive Summary

This repository is a full-stack delivery platform split into two independent codebases:
- Backend API in Laravel (directory: backend)
- Frontend SPA in React + TypeScript (directory: frontend)

Current state appears to be early-to-mid implementation maturity:
- Core auth, customer delivery workflow, operator workflow, lookups, and notifications are implemented.
- No multi-tenancy model is implemented in application code.
- No dedicated admin panel framework (Filament/Nova/Backpack) is implemented.
- API contracts are documented and mostly align, with a few doc-vs-code mismatches.

Evidence:
- backend/composer.json
- frontend/package.json
- backend/routes/api.php
- frontend/src/App.tsx

## Stack And Versions (Detected)
- Backend: PHP ^8.3, Laravel ^13.7, Sanctum ^4.3, Socialite ^5.27, Pest ^4.7 (backend/composer.json)
- Frontend: React ^19.2.6, TypeScript ~6.0.2, Vite ^8.0.12, React Router ^7.15.0, React Query ^5.100.10, Tailwind ^4.3.0 (frontend/package.json)
- Database default: PostgreSQL (backend/config/database.php)

## Architectural Style
- Monolith backend with route + controller + request + policy + service + resource pattern.
- SPA frontend with route-level pages, API modules, and React Query data fetching.
- API versioning via /api/v1 prefix.

Evidence:
- backend/routes/api.php:28
- backend/app/Http/Controllers/Api
- backend/app/Http/Requests
- backend/app/Http/Resources
- backend/app/Services
- frontend/src/api
- frontend/src/pages

# 2. Tech Stack Inventory

| Area | Technology | Version / Evidence | Notes |
|---|---|---|---|
| Backend runtime | PHP | ^8.3 in backend/composer.json | Modern typed PHP features used (enums, attributes). |
| Backend framework | Laravel | ^13.7 in backend/composer.json | API-focused app with custom JSON error rendering. |
| API auth | Laravel Sanctum | ^4.3 in backend/composer.json | Bearer personal access tokens used in controllers/routes. |
| Social login | Laravel Socialite | ^5.27 in backend/composer.json | Google OAuth redirect/callback implemented. |
| Backend testing | Pest + Pest Laravel | ^4.7 / ^4.1 in backend/composer.json | Feature tests present for auth/workflows/lookups/operator. |
| Backend formatting | Laravel Pint | ^1.27 in backend/composer.json | Project instructions require running Pint after PHP edits. |
| Frontend framework | React | ^19.2.6 in frontend/package.json | BrowserRouter + route guards used. |
| Frontend language | TypeScript | ~6.0.2 in frontend/package.json | strict mode enabled in frontend/tsconfig.app.json:9. |
| Frontend bundler | Vite | ^8.0.12 in frontend/package.json | React + Tailwind plugins in frontend/vite.config.ts. |
| Frontend router | react-router-dom | ^7.15.0 in frontend/package.json | Protected customer/operator routes in frontend/src/App.tsx. |
| Frontend server state | @tanstack/react-query | ^5.100.10 in frontend/package.json | QueryClient configured globally. |
| HTTP client | axios | ^1.16.0 in frontend/package.json | Bearer token interceptor and 401 auto-expire event. |
| Frontend forms | react-hook-form + zod | frontend/package.json | Form validation in auth/customer/operator pages. |
| Styling | Tailwind CSS v4 | frontend/package.json, frontend/src/index.css | Utility-first components. |
| Database | PostgreSQL (default) | backend/config/database.php:20,87 | jsonb columns used in migrations. |
| Queue | Laravel queue database driver default | backend/config/queue.php:16,38 | Notifications/jobs implement ShouldQueue. |
| Storage | local + uploads disk abstraction | backend/config/filesystems.php | uploads disk can switch to S3-compatible backend. |
| RBAC package | None external | backend/composer.json | Role enum + middleware + policies are custom. |
| Multi-tenancy package | None detected | backend/composer.json | No tenant models/middleware found in app code. |
| Admin panel package | None detected | backend/composer.json | No Filament/Nova app code present. |

# 3. Current Architecture Map

## Backend Modules
- Auth API controllers: backend/app/Http/Controllers/Api/Auth
- Customer API controllers: backend/app/Http/Controllers/Api/Customer
- Operator API controllers: backend/app/Http/Controllers/Api/Operator
- Lookup API controllers: backend/app/Http/Controllers/Api/Lookup
- Notifications API controller: backend/app/Http/Controllers/Api/NotificationController.php
- Services: pricing, delivery creation, stage progression, OTP (backend/app/Services)
- Policies: delivery request, payment, saved address (backend/app/Policies)
- Resources: unwrapped JsonResource transformers (backend/app/Http/Resources)

## Route Organization
- API route entry: backend/routes/api.php
- Versioning: Route::prefix('v1') (backend/routes/api.php:28)
- Public auth group with throttle auth + otp limiters (backend/routes/api.php:29-40)
- Protected API group with auth:sanctum and throttle:api (backend/routes/api.php:45)
- Operator subgroup with role and token ability middleware (backend/routes/api.php:65)

## Middleware / Exception Flow
- Middleware aliases: role and abilities (backend/bootstrap/app.php:20-24)
- role middleware checks user.role enum value against allowed list (backend/app/Http/Middleware/EnsureUserHasRole.php)
- API JSON error envelope standardized in bootstrap (backend/bootstrap/app.php:26-75)
- Rate limiter definitions in AppServiceProvider (backend/app/Providers/AppServiceProvider.php:40-42)

## Frontend Modules
- App routes and guards: frontend/src/App.tsx, frontend/src/auth/RequireAuth.tsx
- Auth state context + localStorage persistence: frontend/src/auth/AuthContext.tsx, frontend/src/lib/authStorage.ts
- API modules per domain: frontend/src/api/*.ts
- Route-level pages: frontend/src/pages/auth, frontend/src/pages/customer, frontend/src/pages/operator
- Shared UI components: frontend/src/components

## Non-API Surfaces
- Web route is default welcome page only (backend/routes/web.php)
- Console route has default inspire command (backend/routes/console.php)

## See Detailed Files
- Backend deep-dive: 01_BACKEND_CONTEXT.md
- Auth/tenancy/RBAC: 02_AUTH_TENANCY_RBAC_CONTEXT.md
- Database/API: 03_DATABASE_API_CONTEXT.md
- Frontend/admin: 04_FRONTEND_ADMIN_CONTEXT.md
- Testing/security/dev-state: 05_TESTING_SECURITY_CONTEXT.md
- Docs + source-of-truth + handoff: 06_AGENT_HANDOFF_CONTEXT.md

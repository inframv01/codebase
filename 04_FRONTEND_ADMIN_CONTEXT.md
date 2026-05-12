# 8. Frontend Context

## Framework And Build
- React 19 + TypeScript + Vite SPA.
- Strict TypeScript enabled.
- React Query for async state.
- Axios API client with auth interceptors.
- Tailwind CSS v4 styling.

Evidence:
- frontend/package.json
- frontend/tsconfig.app.json:9
- frontend/vite.config.ts
- frontend/src/lib/queryClient.ts
- frontend/src/lib/api.ts

## Routing Structure
- Public auth routes under PublicLayout:
  - /login, /register, /otp, /forgot-password, /reset-password, /auth/google/callback
- Customer authenticated routes under RequireAuth + AppShell:
  - /, /deliveries, /deliveries/new, /deliveries/:uuid, /addresses, /profile, /notifications
- Operator routes under RequireOperator + AppShell:
  - /operator, /operator/deliveries, /operator/deliveries/:uuid, /operator/resources/:resource, /operator/boats/:boatId/schedules

Evidence:
- frontend/src/App.tsx
- frontend/src/auth/RequireAuth.tsx

## Auth State Handling
- Auth context stores token and user in localStorage key maldiv_delivery_auth_v1.
- API request interceptor injects Bearer token.
- API response interceptor clears auth on 401 and dispatches auth:expired event.
- AuthProvider listens for auth:expired and clears React Query cache.

Evidence:
- frontend/src/lib/authStorage.ts
- frontend/src/lib/api.ts
- frontend/src/auth/AuthContext.tsx

## API Client Pattern
- Domain-specific API modules in frontend/src/api:
  - authApi, meApi, lookupApi, addressApi, deliveryApi, notificationApi, operatorApi
- Base URL defaults to http://localhost:8000/api/v1 with VITE_API_URL override.
- Most methods assume unwrapped resource payloads.

Evidence:
- frontend/src/api/*.ts
- frontend/src/lib/api.ts:5

## Forms And Validation Pattern
- React Hook Form + Zod resolver in auth/customer/operator pages.
- Validation error mapping supports backend 422 error shape.
- Multipart FormData used for delivery creation and payment upload.

Evidence:
- frontend/src/pages/auth/*.tsx
- frontend/src/pages/customer/DeliveryCreatePage.tsx
- frontend/src/api/deliveryApi.ts
- frontend/src/lib/errors.ts

## Styling / Component Organization
- Shared components under frontend/src/components.
- Tailwind utility classes and semantic wrappers (Card, Field, Button, Status, Pagination).
- AppShell combines customer + operator nav links based on role.

Evidence:
- frontend/src/components/*.tsx
- frontend/src/index.css

## Frontend-Backend Contract Risks (Important)
- Risk: customer deliveries list endpoint is paginated by backend, but frontend deliveryApi.listDeliveryRequests() expects array.
  - Backend: backend/app/Http/Controllers/Api/Customer/DeliveryRequestController.php:28
  - Frontend: frontend/src/api/deliveryApi.ts:69-72
- Risk: frontend DeliveryStatus type includes payment_uploaded, backend status enum uses payment_review.
  - Frontend: frontend/src/types.ts:33-39
  - Backend: backend/app/Enums/DeliveryStatus.php:7-13
- Risk: API docs and frontend code imply occasional wrapped responses for update resource; backend generally returns unwrapped resources.
  - Frontend workaround in operatorApi.updateResource() checks for data wrapper dynamically.
  - frontend/src/api/operatorApi.ts:55-57

# 9. Admin Panel Context (Frontend Side)

- No separate admin SPA or admin panel frontend package detected.
- Operator/admin share same frontend shell and route guard logic:
  - isOperator computed for operator or admin role.
  - frontend/src/auth/AuthContext.tsx:49
- No central-only vs tenant-aware admin boundary exists; tenancy not implemented.

## Frontend Maturity Snapshot
- Auth and route protection implemented.
- Core customer and operator workflow screens implemented.
- Generic operator resource page suggests dynamic CRUD UI pattern for multiple resource types.
- Frontend README remains mostly Vite template text and is not a full product doc.
  - frontend/README.md

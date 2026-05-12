---
description: "Use when working on any frontend React or TypeScript code, components, hooks, or styles. Covers React 19 conventions, TypeScript rules, and API integration patterns for the standalone SPA."
applyTo: "frontend/**"
---

# Frontend Instructions

## Stack

- **React 19** with `StrictMode` enabled — do not remove it
- **TypeScript ~6** — strict mode via `noUnusedLocals`, `noUnusedParameters`, `erasableSyntaxOnly`
- **Vite 8** — `@vitejs/plugin-react` (Babel/Oxc transform)
- **ESLint** — `react-hooks` and `react-refresh` plugins enforced

## Component Conventions

- One component per file; filename matches the exported component name (`OrderCard.tsx` exports `OrderCard`)
- Use function declarations for top-level components, arrow functions for inline/helper components
- Props typed inline with `interface` (prefer `interface` over `type` for object shapes)
- Avoid default exports except for page-level route components

## TypeScript

- No `any` — use `unknown` and narrow with type guards when type is truly unknown
- Use `import type` for type-only imports (`import type { Order } from './types'`)
- Co-locate types with the component or in a sibling `types.ts` when shared

## State & Side Effects

- Prefer `useState` + `useReducer` for local state; no global state library has been chosen yet
- Keep side effects in `useEffect` minimal — prefer server state managed via `fetch` abstractions
- Extract reusable logic into custom hooks in `src/hooks/`

## API Integration (Laravel backend)

The backend is not yet connected. When wiring up:
- Base URL from `import.meta.env.VITE_API_URL` — never hardcode
- Centralize all API calls in `src/api/` (one file per resource, e.g. `src/api/orders.ts`)
- Use `fetch` with typed response wrappers; only adopt a library (e.g. TanStack Query) if the codebase already uses one
- Laravel CSRF token must be sent as `X-XSRF-TOKEN` header for mutating requests (POST/PUT/DELETE)

## File Structure

```
src/
  api/          # fetch wrappers per resource
  components/   # shared UI components
  hooks/        # custom hooks
  pages/        # route-level components (if routing is added)
  types.ts      # shared TypeScript types
```

## Commands

```bash
cd frontend && npm run dev    # start dev server
cd frontend && npm run build  # type-check + build
cd frontend && npm run lint   # ESLint
```

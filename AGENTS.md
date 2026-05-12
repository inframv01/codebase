# Maldiv Delivery — Agent Instructions

## Project Overview

Delivery platform with two independent codebases in separate directories:

| Directory | Stack |
|-----------|-------|
| `backend/` | Laravel 13, PHP 8.3+, Pest v4, Tailwind CSS v4, Vite |
| `frontend/` | React 19, TypeScript, Vite |

The `backend/` Laravel app serves its own Blade views with Tailwind. The `frontend/` React app is a standalone SPA — the two are **not** wired together yet. Integration via `/api/v1` endpoints is planned.

## Commands

All commands must be run from the correct subdirectory.

### Backend

```bash
# First-time setup
cd backend && composer run setup

# Start dev server (Laravel + queue + logs + Vite, all in one)
cd backend && composer run dev

# Run tests
cd backend && composer run test
# or: php artisan test

# Fix code style (mandatory after changes)
cd backend && ./vendor/bin/pint
```

### Frontend

```bash
# Dev server
cd frontend && npm run dev

# Build (runs type-check first)
cd frontend && npm run build

# Lint
cd frontend && npm run lint
```

## Architecture

### Backend

- **Web Routes**: `backend/routes/web.php` (Blade views, welcome page)
- **API Routes**: `backend/routes/api.php` (**base: `/api/v1` — main integration point**, grouped by domain: Auth, Customer, Operator, Lookup)
- **Controllers**: `backend/app/Http/Controllers/Api/` (grouped by domain)
- **Views**: `backend/resources/views/` (Blade templates)
- **CSS/JS**: compiled via Vite from `backend/resources/css/app.css` and `backend/resources/js/app.js`
- **Fonts**: Bunny Fonts loaded via `laravel-vite-plugin`
- **Tailwind**: v4 via `@tailwindcss/vite` plugin (no `tailwind.config.js`)
- **Scheduled Commands**: `backend/routes/console.php`

### Frontend

- **SPA**: `frontend/src/` — standard React component tree, entry at `main.tsx`
- **File Structure**:
  ```
  src/
    api/         # fetch wrappers per resource (when connected)
    components/  # reusable UI components
    hooks/       # custom hooks
    pages/       # route-level components
    types.ts     # shared types
  ```

### API Design

See [docs/api-contracts/README.md](docs/api-contracts/README.md) for complete API specification.

**Key details:**
- **Auth**: Sanctum personal access tokens (`Authorization: Bearer <token>`)
- **Money**: integer cents fields (`*_cents`)
- **Dates**: ISO8601 strings
- **Validation errors**: HTTP `422` with shape `{ message, errors: { field: [msg] } }`
- **Collections**: unwrapped JSON arrays; paginated endpoints use `{ data, links, meta }`
- **No-content deletes**: HTTP `204` with empty body

**Related docs:**
- [auth.md](docs/api-contracts/auth.md) — Authentication flows
- [me-profile.md](docs/api-contracts/me-profile.md) — User profile endpoints
- [addresses.md](docs/api-contracts/addresses.md) — Address management
- [delivery-requests.md](docs/api-contracts/delivery-requests.md) — Delivery workflows
- [operator.md](docs/api-contracts/operator.md) — Operator dashboard
- [lookups-and-quotes.md](docs/api-contracts/lookups-and-quotes.md) — Atolls, rates, quotes

## Skills

Domain skills are available and **should be activated automatically**:

| Skill | Trigger | File |
|-------|---------|------|
| `laravel-best-practices` | Any Laravel PHP code (controllers, models, migrations, jobs, etc.) | [SKILL.md](.github/skills/laravel-best-practices/SKILL.md) |
| `pest-testing` | Writing or editing any file under `backend/tests/` | [SKILL.md](.github/skills/pest-testing/SKILL.md) |
| `tailwindcss-development` | Writing Tailwind utility classes in Blade or React templates | [SKILL.md](.github/skills/tailwindcss-development/SKILL.md) |
| `react-best-practices` | React/TypeScript code performance or optimization | [SKILL.md](.github/skills/react-best-practices/SKILL.md) |

## Key Conventions

- **Testing**: Use Pest v4 syntax (`test()`, `it()`, `expect()`). Never write PHPUnit-style test classes.
- **PHP style**: Enforced by Laravel Pint (`vendor/bin/pint`). **Run after every PHP change** to ensure compliance.
- **Models**: Use `#[Fillable(...)]` attributes (PHP 8 style), define return type hints, use casts for money/dates.
- **Eloquent**: Eager load relationships to avoid N+1 queries.
- **React**: One component per file; filename matches export name; props typed with `interface`; no `any` types.
- **Consistency**: Follow patterns already used in the codebase before introducing new patterns.

## Setup & Environment

See [backend/README.md](backend/README.md) for detailed setup.

**Quick start:**
```bash
# First time
cd backend && cp .env.example .env && php artisan key:generate

# Install & migrate
cd backend && composer run setup

# Start development
cd backend && composer run dev
cd frontend && npm run dev  # in another terminal
```

**Key environment files:**
- `backend/.env` — Database, mail, queue config (copy from `.env.example`, then generate key)
- `backend/boost.json` — Laravel Boost configuration (skills, MCP, agent settings)
- `frontend/.env` — API URL when frontend is wired to backend (use `VITE_API_URL`)

**Test database:**
- PostgreSQL `maldideliv_test` (config in `backend/phpunit.xml`)
- Auto-created by test suite, use `LazilyRefreshDatabase` trait for isolation

## File Organization Reference

| Path | Purpose | Key Files |
|------|---------|-----------|
| `backend/app/Models/` | Eloquent models | `Delivery.php`, `DeliveryRequest.php`, `User.php` |
| `backend/app/Http/Controllers/Api/` | API controllers (grouped by domain) | `Auth/`, `Customer/`, `Operator/`, `Lookup/` |
| `backend/app/Http/Resources/` | Eloquent API resources | `DeliveryRequestResource.php` |
| `backend/app/Enums/` | PHP enums (TitleCase keys) | `DeliveryStatus.php`, `UserRole.php` |
| `backend/app/Casts/` | Custom Eloquent casts | `MoneyCents.php` |
| `backend/routes/api.php` | **API blueprint** — all endpoints | ✅ Start here for route structure |
| `backend/bootstrap/app.php` | **Exception rendering** — all error shapes | ✅ All API validation/error responses defined here |
| `frontend/src/api/` | API integration (when connected) | `auth.ts`, `orders.ts`, etc. |
| `frontend/src/components/` | Reusable UI components | `Button.tsx`, `Card.tsx` |

## Common Workflows

### Adding a Backend API Endpoint
1. Create migration if schema change needed
2. Create/update model with `#[Fillable(...)]` attributes and casts
3. Create controller in appropriate `app/Http/Controllers/Api/` subdirectory
4. Create form request for validation (`app/Http/Requests/`)
5. Create Eloquent resource for response shape (`app/Http/Resources/`)
6. Add route in `backend/routes/api.php`
7. Write feature tests in `backend/tests/Feature/` (Pest v4 syntax)
8. Run `./vendor/bin/pint` to fix style
9. Run `composer run test` to verify

### Adding a React Component
1. Create `ComponentName.tsx` in `src/components/`
2. Export as function declaration
3. Type props with `interface`
4. Import and use in other components

### Connecting Frontend to Backend API
When frontend is wired to backend:
1. Set `VITE_API_URL` in `frontend/.env`
2. Create fetch wrapper in `src/api/resource.ts`
3. Send `X-XSRF-TOKEN` header for mutations (Laravel Sanctum requirement)
4. Use typed response wrappers for all API calls

## Documentation

- **[docs/api-contracts/](docs/api-contracts/)** — Complete API specification and contracts
- **[backend/CLAUDE.md](backend/CLAUDE.md)** — Laravel Boost guidelines and rules
- **[backend/README.md](backend/README.md)** — Backend setup, stack, environment
- **[frontend/README.md](frontend/README.md)** — Frontend setup
- **[.github/instructions/backend.instructions.md](.github/instructions/backend.instructions.md)** — Backend conventions and skill activation
- **[.github/instructions/frontend.instructions.md](.github/instructions/frontend.instructions.md)** — Frontend conventions and TypeScript rules

## Environment

- `backend/boost.json` configures Laravel Boost (skills, MCP, agent settings)
- Three instruction files auto-activate for their respective domains (see links above)

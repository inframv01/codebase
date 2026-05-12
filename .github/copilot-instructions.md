---
description: Main instructions for AI agents working in Maldiv Delivery codebase
---

# Maldiv Delivery — Chat Instructions

This is a Laravel + React delivery platform with two independent codebases. **Start here for project overview, setup, architecture, and conventions.**

Use this file for repo-level behavior. For domain-specific tasks, prefer the per-folder instruction files below.

## Quick Links

- **[AGENTS.md](../AGENTS.md)** ← Main reference (commands, architecture, API design, conventions)
- **[backend/CLAUDE.md](../backend/CLAUDE.md)** ← Laravel Boost rules and PHP guidelines
- **[docs/api-contracts/](../docs/api-contracts/)** ← Complete API specification
- **[.github/instructions/backend.instructions.md](instructions/backend.instructions.md)** ← Backend conventions (auto-loads for `backend/**`)
- **[.github/instructions/frontend.instructions.md](instructions/frontend.instructions.md)** ← Frontend conventions (auto-loads for `frontend/**`)
- **.github/agents/** ← Specialized architect agent guidance for large refactors

## Essential Commands

```bash
# Backend
cd backend && composer run dev    # Start all services (Laravel, queue, logs, Vite)
cd backend && composer run test   # Run tests (Pest v4)
cd backend && ./vendor/bin/pint   # Fix code style (run after changes)

# Frontend
cd frontend && npm run dev        # Dev server with HMR
cd frontend && npm run build      # Type-check + build
```

## Key Points

- **Two codebases**: Backend (Laravel 13, PHP 8.3) in `/backend/`, Frontend (React 19, TypeScript) in `/frontend/`
- **Backend API**: Routes at `/api/v1`, documented in [docs/api-contracts/](../docs/api-contracts/)
- **Not integrated yet**: Frontend and backend are independent; frontend will eventually call backend API
- **PHP style enforced**: Pint after every change — `./vendor/bin/pint`
- **Testing**: Pest v4 only — `test()`, `it()`, `expect()` syntax (no PHPUnit classes)
- **TypeScript strict**: No `any` types, use `import type` for type-only imports

## Skills (Auto-Activate)

- `laravel-best-practices` → Laravel PHP code
- `pest-testing` → Tests in `backend/tests/`
- `tailwindcss-development` → Tailwind classes
- `react-best-practices` → React/TypeScript optimization

See [AGENTS.md](../AGENTS.md) for full reference.

---
description: "Use when working on any backend Laravel PHP code, Blade views, or backend tests. Covers skill activation, conventions, and tooling for the Laravel 13 / PHP 8.3 backend."
applyTo: "backend/**"
---

# Backend Instructions

## Skill Activation

Load and apply the relevant skill automatically based on the task:

| Task | Skill to load |
|------|--------------|
| Any PHP code — controllers, models, migrations, jobs, services, policies | [laravel-best-practices](../skills/laravel-best-practices/SKILL.md) |
| Writing or editing any file under `backend/tests/` | [pest-testing](../skills/pest-testing/SKILL.md) |
| Writing Tailwind utility classes in Blade templates | [tailwindcss-development](../skills/tailwindcss-development/SKILL.md) |

## Key Conventions

- **Tests**: Pest v4 syntax only — `test()`, `it()`, `expect()`. No PHPUnit-style classes.
- **Models**: Always define `$fillable` or `$guarded`.
- **Code style**: Run `./vendor/bin/pint` after every PHP change.
- **Tailwind**: v4 via `@tailwindcss/vite` — no `tailwind.config.js` exists.
- **Consistency**: Match patterns already in the codebase before introducing new ones.

## Commands

```bash
cd backend && composer run test   # run tests
cd backend && ./vendor/bin/pint   # fix code style
cd backend && composer run dev    # start dev server
```

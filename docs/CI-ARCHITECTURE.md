# CI Architecture

This document describes the GitHub Actions CI/CD pipeline for the Maldiv Delivery platform.

## Overview

The CI pipeline is designed to:
- **Prevent broken deployments** — Lint, type-check, unit tests, integration tests required to merge
- **Detect regressions early** — Comprehensive test coverage runs on every PR
- **Enforce code quality** — Automated linting, type checking, static analysis
- **Ensure security** — Dependency scanning, secret detection
- **Optimize for speed** — Parallel jobs, aggressive caching, fail-fast strategy

**Total runtime:** ~20 minutes (most checks pass in <10 minutes on happy path)

## Workflow Files

| File | Purpose | Trigger |
|------|---------|---------|
| `.github/workflows/ci.yml` | Main CI pipeline | Push to main/develop, all PRs |
| `.github/workflows/playwright.yml` | Optional E2E tests | Push to main/develop, all PRs, manual |

## Pipeline Architecture

```
┌─ Setup (install dependencies, cache)
│
├─ Lint & Type Check (parallel)
│  ├─ Laravel Pint (PHP style)
│  ├─ ESLint (JavaScript)
│  └─ TypeScript compiler
│
├─ Unit Tests (parallel)
│  ├─ Pest (backend)
│  └─ Vitest (frontend)
│
├─ Build (parallel)
│  ├─ Backend Vite assets
│  └─ Frontend Vite bundle
│
├─ Integration Tests
│  └─ Pest feature tests (PostgreSQL service)
│
├─ Security (parallel, non-blocking)
│  ├─ PHP dependency audit
│  ├─ Node dependency audit
│  └─ Secret scanning
│
├─ Static Analysis (non-blocking)
│  └─ PHPStan level 5
│
└─ Report (artifacts, PR comments)
```

## Detailed Job Descriptions

### Setup Job

**Purpose:** Install and cache all dependencies

**Key steps:**
1. Checkout code
2. Setup PHP 8.3 + Composer
3. Setup Node 20
4. Download and cache Composer dependencies (keyed by `composer.lock`)
5. Download and cache npm dependencies (keyed by `package-lock.json`)
6. Install Composer dependencies
7. Install frontend npm dependencies

**Cache keys:**
- `composer-${{ runner.os }}-${{ hashFiles('backend/composer.lock') }}`
- `npm-${{ runner.os }}-${{ hashFiles('frontend/package-lock.json', 'backend/package.json') }}`

**Duration:** 5-10 minutes (most time spent on first run or after dependency updates)

### Lint Job

**Purpose:** Enforce code style and standards

**Steps:**
1. Restore cached dependencies
2. Run Laravel Pint (PHP code style)
3. Run ESLint (JavaScript style)

**Failure behavior:** BLOCKS merge

**Duration:** ~3 minutes

**Pint Configuration:**
- Uses Laravel Pint v1.27+
- Enforces PSR-12 style with Laravel conventions
- Run locally: `cd backend && ./vendor/bin/pint --check`
- Fix locally: `cd backend && ./vendor/bin/pint`

**ESLint Configuration:**
- Uses strict TypeScript ESLint rules
- Checks all JS/TS files in frontend
- Run locally: `cd frontend && npm run lint`

### Type Check Job

**Purpose:** Verify TypeScript compilation

**Steps:**
1. Restore cached npm dependencies
2. Run `tsc -b` (TypeScript compiler)

**Failure behavior:** BLOCKS merge

**Duration:** ~2 minutes

**Run locally:** `cd frontend && tsc -b`

### Unit Tests Job

**Purpose:** Run isolated tests (no database required)

**Backend (Pest):**
- Test suite: `Unit`
- Database: Not used
- Coverage: Collected with XDebug
- Duration: ~5 minutes

**Frontend (Vitest):**
- Test environment: jsdom
- Coverage: Collected
- Duration: ~5 minutes

**Failure behavior:** BLOCKS merge

**Key assertions:**
- Pest unit tests in `backend/tests/Unit/`
- Vitest tests in `frontend/src/__tests__/`
- Minimum coverage thresholds enforced (see `vitest.config.ts`)

**Run locally:**
```bash
cd backend && php artisan test --testsuite=Unit --coverage
cd frontend && npm run test:unit
```

### Build Job

**Purpose:** Verify both frontend and backend build successfully

**Steps:**
1. Restore cached dependencies
2. Build backend Vite assets (`npm run build`)
3. Build frontend Vite bundle (`npm run build`)

**Failure behavior:** BLOCKS merge

**Duration:** ~5 minutes

**Key artifacts uploaded:**
- `backend/public/build/*` — compiled backend assets
- `frontend/dist/*` — compiled frontend bundle

**Run locally:**
```bash
cd backend && npm run build
cd frontend && npm run build
```

### Integration Tests Job

**Purpose:** Test API endpoints with real database

**Database:**
- PostgreSQL 16 Alpine (service container)
- Database: `maldideliv_test`
- User: `app_gm_user` (password in `phpunit.xml`)
- Auto-initialized from migrations

**Steps:**
1. Wait for PostgreSQL to be ready
2. Run `php artisan migrate:refresh --force` (schema setup)
3. Run `php artisan db:seed --class=TestSeeder` (optional data)
4. Run Pest feature tests with coverage
5. Upload coverage report artifact

**Test suites:**
- `Feature` tests in `backend/tests/Feature/Api/`
- Tests use `LazilyRefreshDatabase` trait for isolation
- Each test runs in a transaction (rolled back automatically)

**Failure behavior:** BLOCKS merge

**Duration:** ~10 minutes

**Run locally:**
```bash
cd backend
php artisan migrate:refresh --force
php artisan test --testsuite=Feature --coverage
```

### Static Analysis Job

**Purpose:** Detect potential code quality issues (non-blocking)

**Tools:**
- PHPStan level 5
- Detects: type errors, undefined vars, deprecated calls

**Failure behavior:** NON-BLOCKING (doesn't block merge, but visible in job status)

**Duration:** ~5-10 minutes

**Run locally:**
```bash
cd backend && ./vendor/bin/phpstan analyse app --level=5
```

### Security Job

**Purpose:** Detect vulnerabilities and secrets (non-blocking)

**Steps:**
1. Check PHP dependencies: `composer audit`
2. Check Node dependencies: `npm audit`
3. Scan for secrets: TruffleHog

**Failure behavior:** NON-BLOCKING (informational only)

**Duration:** ~3 minutes

**Run locally:**
```bash
cd backend && composer audit
cd frontend && npm audit
```

### Playwright E2E Tests (Separate Workflow)

**File:** `.github/workflows/playwright.yml`

**Purpose:** Browser automation testing (optional approval gate)

**Key features:**
- Runs on all push and PR events
- Can be triggered manually (`workflow_dispatch`)
- Tests run against live backend + frontend servers
- Retries: 2x per test on failure (flaky test handling)
- Screenshots/video on failure
- Test results uploaded to artifacts
- Approval gate: Optional (doesn't block merge by default)

**Duration:** ~20-30 minutes (includes server startup, browser setup)

**Tests locations:** `frontend/tests/e2e/*.spec.ts`

**Run locally:**
```bash
npm run test:e2e        # Run all E2E tests
npm run test:e2e:ui     # Interactive UI mode
npm run test:e2e:debug  # Debug mode with inspector
```

## Caching Strategy

### Composer Cache

**Key:** `composer-${{ runner.os }}-${{ hashFiles('backend/composer.lock') }}`

**Cached path:** `backend/vendor/`

**Hit rate:** ~95% (except after composer.lock changes)

**Restored by:** Setup, Lint, Unit Tests, Build, Integration Tests, Security, Static Analysis

### npm Cache

**Key:** `npm-${{ runner.os }}-${{ hashFiles('frontend/package-lock.json', 'backend/package.json') }}`

**Cached paths:**
- `frontend/node_modules/`
- `backend/node_modules/`

**Hit rate:** ~95% (except after package-lock.json changes)

**Restored by:** Setup, Unit Tests, Build, Playwright

## Test Database Configuration

**Environment variables** (in `phpunit.xml` and CI workflows):
- `DB_CONNECTION`: `pgsql`
- `DB_HOST`: `127.0.0.1` (localhost in CI)
- `DB_PORT`: `5432`
- `DB_DATABASE`: `maldideliv_test`
- `DB_USERNAME`: `app_gm_user`
- `DB_PASSWORD`: `sx3!asvc!asd12`

**Isolation mechanism:**
- Each test runs in a transaction
- Transaction is rolled back after test completes
- No test data persists between tests
- No cross-test pollution

**Initial setup:**
- `php artisan migrate:refresh --force` — runs all migrations
- `php artisan db:seed --force` — optional seeding
- Database is dropped and recreated if needed

## Environment Variables

### In CI Workflows

**Set via:**
1. `.env.ci` file (backend)
2. `phpunit.xml` `<env>` sections
3. GitHub Actions secrets (for sensitive values)

**Never exposed in logs** (GitHub Actions masking enabled)

### For Local Development

**Backend:**
```bash
cp backend/.env.example backend/.env
# Edit .env with local PostgreSQL credentials
php artisan key:generate
```

**Frontend:**
```bash
# .env not required, but you can set:
VITE_API_BASE_URL=http://localhost:8000
# Legacy VITE_API_URL is also accepted.
```

## GitHub Actions Secrets

**Required secrets** (defined in repository settings):
- None currently required (credentials in `phpunit.xml`)

**To add secrets:**
1. Go to GitHub repository → Settings → Secrets and variables → Actions
2. Click "New repository secret"
3. Add secret name and value
4. Use in workflow: `${{ secrets.SECRET_NAME }}`

## Branch Protection Rules

**Recommended configuration** (in repository → Settings → Branches):

| Check | Required | Allow Override |
|-------|----------|-----------------|
| Lint | ✅ Yes | ❌ No |
| Type Check | ✅ Yes | ❌ No |
| Unit Tests | ✅ Yes | ❌ No |
| Build | ✅ Yes | ❌ No |
| Integration Tests | ✅ Yes | ❌ No |
| PHPStan | ❌ No | N/A |
| Security | ❌ No | N/A |
| Playwright E2E | ❌ No | N/A |

**Dismiss stale reviews:** ✅ Yes

**Require code owner approval:** Consider for critical paths

## Performance Optimization

### Parallel Execution

**Lint & Type Check** run in parallel (independent):
- Lint takes ~3 min
- Type Check takes ~2 min
- Total: ~3 min (not 5)

**Unit Tests** run in parallel:
- Backend unit tests: ~5 min
- Frontend unit tests: ~3 min
- Total: ~5 min (both run simultaneously)

**Build** runs in parallel:
- Backend build: ~3 min
- Frontend build: ~4 min
- Total: ~4 min (both run simultaneously)

### Critical Path

1. Setup: 5-10 min
2. Lint + Type Check (parallel): 3 min
3. Unit Tests (parallel): 5 min
4. Build (parallel): 4 min
5. Integration Tests: 10 min
6. **Total: ~25 minutes** (best case)

### Cache Optimization

**First run:** ~30-40 minutes (dependencies downloaded)

**Subsequent runs:** ~20-25 minutes (cache hit)

**Invalidation:**
- Composer cache invalidated when `composer.lock` changes
- npm cache invalidated when `package-lock.json` or `package.json` changes
- Cache keys include hash, so new dependency versions = new cache key

**To clear cache:**
- GitHub UI: Settings → Actions → Caching → Clear all
- Or push a change to lock file and revert

## Debugging Failed Workflows

### View Workflow Logs

1. Go to GitHub repo → Actions
2. Click the failed workflow run
3. Click the failed job
4. Expand steps to see logs

### Common Failures

| Error | Cause | Fix |
|-------|-------|-----|
| `Lint failed` | Code style issue | Run `./vendor/bin/pint` locally |
| `Type error` | TypeScript error | Run `tsc -b` locally |
| `Test failed` | Test assertion | Check test output, run locally |
| `Database timeout` | PostgreSQL slow | Check CI logs, may be transient |
| `Cache miss` | Dependencies not installed | Cache will rebuild, retry workflow |

### Run Workflow Locally

Test workflow locally using [act](https://github.com/nektos/act):

```bash
# Install act
brew install act  # macOS

# Run workflow
act push

# Run specific job
act push --job=lint
```

## Artifact Management

### Generated Artifacts

**Coverage reports:**
- `coverage-reports/` — Unit test coverage (XML + HTML)
- `coverage-integration/` — Integration test coverage

**Build output:**
- `build-output/` — Compiled assets (kept 90 days)

**Playwright reports:**
- `playwright-report/` — E2E test report (kept 30 days)

**Download artifacts:**
1. Go to GitHub Actions run
2. Click "Artifacts" at bottom
3. Download ZIP

### Retention Policy

- Coverage reports: 90 days
- Build artifacts: 90 days
- Playwright reports: 30 days
- Logs: 60 days

## Security Considerations

### Secret Masking

**Enabled for:**
- Database passwords
- API tokens
- Any value marked as `secret:`

**In workflow logs, secrets are replaced with `***`**

### Code Scanning

**Tools running:**
- Dependency audit (composer + npm)
- TruffleHog (secret detection)
- PHPStan (code quality)

**Not enabled (future consideration):**
- GitHub Advanced Security (code scanning)
- SonarCloud (SAST)
- Snyk (supply chain security)

## Troubleshooting

### Workflow Won't Trigger

**Check:**
1. Workflow file syntax (`act validate -l` or linter)
2. Event trigger (on.push, on.pull_request, etc.)
3. Branch filter (push.branches, pull_request.branches)

### Jobs Timeout

**Default timeout:** 360 minutes

**Increase if needed:**
```yaml
jobs:
  long-job:
    timeout-minutes: 600
```

### Cache Not Working

**Verify cache hit:**
- Check workflow logs for "Cache hit"
- If "Cache miss", dependencies will be re-installed
- Next run should hit cache

**Force cache rebuild:**
- Change `cache-key` in workflow
- Or clear cache in repository settings

### Database Connection Error

**Check:**
1. PostgreSQL service is healthy: Check workflow logs
2. Database exists: `createdb maldideliv_test`
3. User exists: `psql -U app_gm_user`
4. Credentials match: Check `phpunit.xml`

## Cost Optimization

### GitHub Actions Usage

**Free tier:** 2000 minutes/month for private repos

**Current pipeline cost:**
- ~25 minutes per PR (1 run)
- ~25 minutes per push to main/develop (1 run)
- E2E workflow: ~30 minutes (optional)

**Example: 10 PRs/week = 250 minutes**

### Cost-Saving Tips

1. **Don't run E2E on every PR** — Use approval gate
2. **Cache aggressively** — Current setup is optimized
3. **Parallelize jobs** — Current setup is optimized
4. **Skip unnecessary checks** — Only what matters
5. **Use matrix strategy sparingly** — Only for cross-platform testing (future)

## Future Improvements

| Feature | Effort | Impact | Notes |
|---------|--------|--------|-------|
| Codecov integration | Low | Medium | Track coverage over time |
| SonarCloud SAST | Medium | High | Advanced code quality |
| Load testing | High | Low | Nice-to-have, not critical |
| Docker image build | Medium | Low | Useful for deployment |
| Scheduled security scan | Low | Medium | Detect vulnerabilities outside CI |

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [GitHub Actions Caching](https://docs.github.com/en/actions/using-workflows/caching-dependencies-to-speed-up-workflows)
- [Laravel Pint](https://laravel.com/docs/pint)
- [Pest Documentation](https://pestphp.com)
- [Playwright Documentation](https://playwright.dev)
- [PHPStan Documentation](https://phpstan.org)

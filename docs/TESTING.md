# Testing Guide

This guide covers how to run tests locally and write new tests for the Maldiv Delivery platform.

## Quick Start

### Backend Tests (Pest)

```bash
# Run all tests
cd backend && composer run test

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Api/Auth/AuthenticationTest.php

# Run tests matching pattern
php artisan test --filter=LoginTest

# Watch mode (auto-rerun on changes)
php artisan test --watch
```

### Frontend Tests

```bash
# Run unit tests (Vitest)
cd frontend && npm run test:unit

# Run unit tests in watch mode with UI
npm run test:unit:watch

# Run E2E tests (Playwright)
npm run test:e2e

# Run E2E tests with UI
npm run test:e2e:ui

# Debug E2E tests (opens Playwright Inspector)
npm run test:e2e:debug

# Run specific E2E test file
npm run test:e2e -- tests/e2e/auth.spec.ts
```

## Local Development Environment

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- PostgreSQL 16+
- Git

### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Create .env from example
cp .env.example .env

# Generate app key
php artisan key:generate

# Setup test database (one time)
createdb maldideliv_test
psql -d maldideliv_test -c "CREATE USER app_gm_user WITH PASSWORD 'sx3!asvc!asd12'; ALTER ROLE app_gm_user CREATEDB;"

# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed
```

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm ci

# Start dev server
npm run dev

# Dev server is available at http://localhost:5173
```

### Run Full Stack Locally

```bash
# Terminal 1: Backend
cd backend
composer run dev

# Terminal 2: Frontend (in new terminal)
cd frontend
npm run dev
```

This starts:
- Laravel API server: http://localhost:8000
- Queue listener: processing jobs
- Log viewer: monitoring logs
- Vite dev server: http://localhost:5173 (frontend)

## Test Database Setup

### PostgreSQL Installation

**macOS (Homebrew):**
```bash
brew install postgresql@16
brew services start postgresql@16
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt install postgresql-16
sudo systemctl start postgresql
```

**Windows (WSL2):**
```bash
# Inside WSL2
sudo apt install postgresql-16
sudo service postgresql start
```

### Create Test Database

```bash
createdb maldideliv_test
psql -d maldideliv_test -c "CREATE USER app_gm_user WITH PASSWORD 'sx3!asvc!asd12';"
psql -d maldideliv_test -c "ALTER ROLE app_gm_user CREATEDB;"
psql -d maldideliv_test -c "GRANT ALL PRIVILEGES ON DATABASE maldideliv_test TO app_gm_user;"
```

## Writing Tests

### Backend: Pest Feature Tests

Feature tests are integration tests that test API endpoints with a real database.

**Location:** `backend/tests/Feature/Api/`

**Example:**
```php
<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Customer Delivery Requests API', function () {
    beforeEach(function () {
        $this->user = User::factory()->customer()->create();
        Sanctum::actingAs($this->user, ['*']);
    });

    it('should list customer delivery requests', function () {
        $response = $this->getJson('/api/v1/delivery-requests');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'status', 'created_at'],
                ],
            ]);
    });

    it('should create delivery request', function () {
        $response = $this->postJson('/api/v1/delivery-requests', [
            'pickup_location' => 'Malé',
            'delivery_location' => 'Addu',
            'weight_kg' => 5.5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('delivery_requests', [
            'user_id' => $this->user->id,
        ]);
    });
});
```

**Key Patterns:**

- **Setup:** Use `beforeEach()` for common setup
- **Authentication:** Use `Sanctum::actingAs($user)` to test authenticated endpoints
- **Database:** Use `LazilyRefreshDatabase` trait (auto-applied in Feature tests)
- **Assertions:** Use `assertStatus()`, `assertJson()`, `assertDatabaseHas()`
- **Factories:** Use `User::factory()->customer()->create()` to create test data

### Backend: Unit Tests

Unit tests test individual functions or classes in isolation.

**Location:** `backend/tests/Unit/`

**Example:**
```php
<?php

use App\Services\PricingService;

describe('PricingService', function () {
    it('should calculate shipping cost correctly', function () {
        $service = new PricingService();
        
        $cost = $service->calculate(
            weight: 5.5,
            distance: 100,
            zone: 'outer_islands'
        );
        
        expect($cost)->toBeGreaterThan(0);
    });
});
```

### Frontend: Unit Tests (Vitest)

Unit tests test React components, hooks, and utilities.

**Location:** `frontend/src/__tests__/` or co-located with components as `.spec.ts`

**Example Component Test:**
```typescript
import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import Button from '../Button'

describe('Button Component', () => {
  it('should render button with label', () => {
    render(<Button>Click me</Button>)
    expect(screen.getByRole('button')).toHaveTextContent('Click me')
  })

  it('should call onClick handler', async () => {
    const onClick = vi.fn()
    render(<Button onClick={onClick}>Click me</Button>)
    
    await userEvent.click(screen.getByRole('button'))
    expect(onClick).toHaveBeenCalled()
  })
})
```

### Frontend: E2E Tests (Playwright)

E2E tests simulate real user interactions with the running application.

**Location:** `frontend/tests/e2e/`

**Example:**
```typescript
import { test, expect } from '@playwright/test'

test.describe('Delivery Request Flow', () => {
  test('should create delivery request', async ({ page }) => {
    // Login
    await page.goto('/login')
    await page.fill('input[name="email"]', 'customer@example.com')
    await page.fill('input[name="password"]', 'password123')
    await page.click('button[type="submit"]')

    // Navigate to create delivery
    await page.goto('/delivery-requests/create')

    // Fill form
    await page.fill('input[name="pickup_location"]', 'Malé')
    await page.fill('input[name="delivery_location"]', 'Addu')
    await page.fill('input[name="weight"]', '5.5')

    // Submit
    await page.click('button[type="submit"]')

    // Verify success
    await expect(page).toHaveURL(/\/delivery-requests\/\d+/)
    await expect(page.locator('text=Delivery request created')).toBeVisible()
  })
})
```

## Database Seeding for Tests

### Creating Test Factories

Test factories generate fake data for tests.

**Location:** `backend/database/factories/`

**Example:**
```php
<?php

namespace Database\Factories;

use App\Models\DeliveryRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryRequestFactory extends Factory
{
    protected $model = DeliveryRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pickup_location' => $this->faker->city(),
            'delivery_location' => $this->faker->city(),
            'weight_kg' => $this->faker->randomFloat(1, 1, 100),
            'status' => 'pending',
        ];
    }
}
```

### Using Factories in Tests

```php
// Create single model
$delivery = DeliveryRequest::factory()->create();

// Create multiple
$deliveries = DeliveryRequest::factory(5)->create();

// Create with specific attributes
$delivery = DeliveryRequest::factory()->create([
    'status' => 'completed',
]);

// Use relationships
$delivery = DeliveryRequest::factory()
    ->for(User::factory()->operator())
    ->create();
```

## Test Database Isolation

Tests use `LazilyRefreshDatabase` trait to isolate each test:

- Database is rolled back after each test
- No test can affect another test's data
- Tests run in transactions for speed

If you need to seed data for a test:

```php
it('should work with seeded data', function () {
    // Seed only runs once per test file
    $this->seed(TestSeeder::class);
    
    // Your test here
});
```

## Debugging Tests

### Backend: Pest Tests

```bash
# Run with verbose output
php artisan test --verbose

# Stop on first failure
php artisan test --bail

# Run specific line
php artisan test --filter=testLoginWithValidCredentials

# Use pdb/xdebug
# Add to test: dd($response->json())
```

### Frontend: Playwright Tests

```bash
# Debug mode opens Playwright Inspector
npm run test:e2e:debug

# UI mode shows test runner and inspector
npm run test:e2e:ui

# View generated report
npx playwright show-report

# Trace failed tests
# Traces are automatically saved on failure in .playwright/trace
```

## Common Issues

### Database Connection Issues

```bash
# Check PostgreSQL is running
psql -d maldideliv_test -c "SELECT 1"

# Test with credentials
PGPASSWORD=sx3!asvc!asd12 psql -U app_gm_user -d maldideliv_test -h 127.0.0.1 -c "SELECT 1"
```

### Playwright Timeout

If tests timeout waiting for server:

```bash
# Ensure backend is running on port 8000
curl http://localhost:8000/up

# Check Laravel logs
cd backend && php artisan pail
```

### Test Hangs

```bash
# Increase timeout
php artisan test --timeout=300

# Or for Playwright
npm run test:e2e -- --timeout=60000
```

## CI Testing

Tests run automatically in CI (GitHub Actions):

- **On PR:** Lint, type check, unit tests, build (required)
- **On main:** All above + integration tests + E2E tests (optional)

See [CI-ARCHITECTURE.md](./CI-ARCHITECTURE.md) for details.

## Test Coverage

### Backend Coverage

```bash
php artisan test --coverage
# Report in: backend/coverage/

# View HTML report
open coverage/index.html
```

### Frontend Coverage

```bash
npm run test:unit
# Report in: frontend/coverage/

# View HTML report
open coverage/index.html
```

## Performance

### Speed Up Tests

1. **Use factories instead of seeders**
   ```php
   // Slow
   $this->seed(UsersSeeder::class);
   
   // Fast
   User::factory(100)->create();
   ```

2. **Disable unnecessary services**
   - Check `phpunit.xml` for test env settings
   - Use `QUEUE_CONNECTION=sync` for tests

3. **Parallel execution**
   ```bash
   php artisan test --parallel
   ```

4. **Filter tests**
   ```bash
   # Only run changed tests
   php artisan test --filter=
   ```

## Resources

- [Pest Documentation](https://pestphp.com)
- [Playwright Documentation](https://playwright.dev)
- [Vitest Documentation](https://vitest.dev)
- [Laravel Testing Docs](https://laravel.com/docs/testing)

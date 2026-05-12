# E2E Testing Guide

This guide covers end-to-end (E2E) testing using Playwright for the Maldiv Delivery platform.

## Overview

E2E tests simulate real user interactions with the application:
- ✅ Test critical workflows end-to-end (login → action → success)
- ✅ Verify authorization (users can/cannot access pages)
- ✅ Test error handling (validation, server errors)
- ✅ Check responsive behavior (mobile, tablet, desktop)
- ✅ Validate integration between frontend and backend

**Location:** `frontend/tests/e2e/*.spec.ts`

**Run locally:** `npm run test:e2e`

**Debug mode:** `npm run test:e2e:debug`

## Installation & Setup

### Prerequisites

```bash
# Node 20+
node --version

# Browsers installed
npx playwright install

# Backend running on port 8000
cd backend && php artisan serve --port=8000
```

### Configuration

**File:** `frontend/playwright.config.ts`

**Key settings:**
- `baseURL`: http://localhost:8000 (your backend)
- `timeout`: 30 seconds per test
- `retries`: 2 (in CI only)
- `workers`: 1 in CI, auto in dev
- `trace`: Automatic on first retry
- `screenshot`: Only on failure
- `video`: Only on failure

## Running Tests

### Local Development

```bash
# Run all E2E tests
npm run test:e2e

# Run specific test file
npm run test:e2e -- tests/e2e/auth.spec.ts

# Run tests matching pattern
npm run test:e2e -- --grep="login"

# Run in UI mode (recommended for development)
npm run test:e2e:ui

# Debug mode (opens Playwright Inspector)
npm run test:e2e:debug

# Run on specific browsers
npm run test:e2e -- --project=chromium
npm run test:e2e -- --project=firefox
npm run test:e2e -- --project=webkit
```

### CI Pipeline

Tests run in `.github/workflows/playwright.yml`:

```bash
# On GitHub Actions:
# - PostgreSQL service starts
# - Laravel server starts on port 8000
# - Frontend is built
# - Tests run with retries=2
# - Results uploaded to artifacts
```

## Writing Tests

### Test Structure

```typescript
import { test, expect } from '@playwright/test'

test.describe('Feature Name', () => {
  test('should do something', async ({ page }) => {
    // Setup
    await page.goto('/login')

    // Action
    await page.fill('input[name="email"]', 'user@example.com')
    await page.click('button[type="submit"]')

    // Assertion
    await expect(page).toHaveURL('/dashboard')
  })
})
```

### Core Concepts

#### Page Object

The `page` object represents a browser tab:

```typescript
// Navigation
await page.goto('/login')
await page.goBack()
await page.reload()

// Wait for navigation
await page.waitForURL('/dashboard')

// URL assertion
await expect(page).toHaveURL('/dashboard')
```

#### Locators

Find elements on the page:

```typescript
// By role (preferred for accessibility)
page.getByRole('button', { name: 'Submit' })
page.getByRole('textbox', { name: 'Email' })
page.getByLabel('Password')

// By CSS selector
page.locator('input[name="email"]')
page.locator('.error-message')

// By text
page.getByText('Welcome!')
page.getByText(/exact|regex/i)

// By placeholder
page.getByPlaceholder('Enter email...')
```

#### Actions

Interact with elements:

```typescript
// Type
await page.fill('input[name="email"]', 'test@example.com')
await page.locator('input').type('text', { delay: 100 })

// Click
await page.click('button[type="submit"]')
await page.getByRole('button').click()

// Select
await page.selectOption('select', 'value')

// Check/uncheck
await page.check('input[type="checkbox"]')
await page.uncheck('input[type="checkbox"]')

// Hover
await page.hover('button')

// Drag
await page.dragAndDrop('#source', '#target')
```

#### Assertions

Verify state:

```typescript
// URL
expect(page).toHaveURL('/dashboard')

// Element visible
expect(locator).toBeVisible()

// Element text
expect(locator).toContainText('Welcome!')
expect(locator).toHaveText('Exact text')

// Element count
expect(page.locator('button')).toHaveCount(3)

// Form value
expect(page.locator('input[name="email"]')).toHaveValue('test@example.com')

// Class
expect(locator).toHaveClass('active')

// HTML attribute
expect(locator).toHaveAttribute('disabled')

// Attribute value
expect(locator).toHaveAttribute('href', '/page')
```

#### Waits

Wait for conditions:

```typescript
// Wait for element
await page.waitForSelector('button.submit')
await page.locator('button.submit').waitFor({ state: 'visible' })

// Wait for navigation
await page.waitForNavigation()

// Wait for load
await page.waitForLoadState('networkidle')

// Wait for function
await page.waitForFunction(() => document.querySelectorAll('li').length > 0)

// Timeout
await page.locator('button').click({ timeout: 5000 })
```

## Test Examples

### Authentication Flow

```typescript
import { test, expect } from '@playwright/test'

test.describe('Authentication', () => {
  test('should register new customer', async ({ page }) => {
    await page.goto('/register')

    // Fill registration form
    await page.fill('input[name="first_name"]', 'John')
    await page.fill('input[name="last_name"]', 'Doe')
    await page.fill('input[name="email"]', 'john@example.com')
    await page.fill('input[name="phone"]', '+9601234567')
    await page.fill('input[name="password"]', 'SecurePass123!')
    await page.fill('input[name="password_confirmation"]', 'SecurePass123!')

    // Submit form
    await page.click('button[type="submit"]')

    // Verify success
    await expect(page).toHaveURL('/dashboard')
    await expect(page.getByText('Welcome, John!')).toBeVisible()
  })

  test('should login with valid credentials', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[name="email"]', 'customer@example.com')
    await page.fill('input[name="password"]', 'password123')
    await page.click('button[type="submit"]')

    await expect(page).toHaveURL('/dashboard')
  })

  test('should show error on invalid login', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[name="email"]', 'wrong@example.com')
    await page.fill('input[name="password"]', 'wrong')
    await page.click('button[type="submit"]')

    await expect(page.locator('.error-message')).toContainText('Invalid credentials')
  })

  test('should logout successfully', async ({ page }) => {
    await page.goto('/dashboard')

    // Logout (implementation depends on your app)
    await page.click('button[aria-label="User menu"]')
    await page.click('text=Logout')

    await expect(page).toHaveURL('/login')
  })
})
```

### Authorization Tests

```typescript
test.describe('Authorization', () => {
  test('should redirect to login for unauthenticated users', async ({ page }) => {
    await page.goto('/dashboard')

    // Should redirect to login
    await expect(page).toHaveURL('/login')
  })

  test('should deny operator access to customer routes', async ({ page }) => {
    // Login as operator
    await page.goto('/login')
    await page.fill('input[name="email"]', 'operator@example.com')
    await page.fill('input[name="password"]', 'password')
    await page.click('button[type="submit"]')

    // Try to access customer route
    await page.goto('/delivery-requests/create')

    // Should be denied or redirected
    await expect(page).toHaveURL(/\/404|\/unauthorized|\/dashboard/)
  })

  test('should allow admin access to all areas', async ({ page }) => {
    // Login as admin
    await page.goto('/login')
    await page.fill('input[name="email"]', 'admin@example.com')
    await page.fill('input[name="password"]', 'password')
    await page.click('button[type="submit"]')

    // Access customer area
    await page.goto('/delivery-requests')
    await expect(page).toHaveURL('/delivery-requests')

    // Access operator area
    await page.goto('/operator/atolls')
    await expect(page).toHaveURL('/operator/atolls')
  })
})
```

### Business Workflow Tests

```typescript
test.describe('Delivery Request Flow', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login')
    await page.fill('input[name="email"]', 'customer@example.com')
    await page.fill('input[name="password"]', 'password123')
    await page.click('button[type="submit"]')
    await page.waitForURL('/dashboard')
  })

  test('should create delivery request', async ({ page }) => {
    // Navigate to create delivery
    await page.click('text=Create Delivery')

    // Fill form
    await page.fill('input[name="pickup_location"]', 'Malé')
    await page.fill('input[name="delivery_location"]', 'Addu')
    await page.fill('input[name="weight"]', '5.5')
    await page.selectOption('select[name="priority"]', 'normal')

    // Submit
    await page.click('button[type="submit"]')

    // Verify success
    await expect(page.getByText('Delivery request created')).toBeVisible()
    await expect(page).toHaveURL(/\/delivery-requests\/\d+/)

    // Verify details
    await expect(page.getByText('Malé')).toBeVisible()
    await expect(page.getByText('Addu')).toBeVisible()
  })

  test('should cancel delivery request', async ({ page }) => {
    // Navigate to delivery request
    await page.goto('/delivery-requests')
    await page.click('text=Delivery #1')

    // Cancel delivery
    await page.click('button[data-action="cancel"]')
    await page.click('button[aria-label="Confirm"]')

    // Verify cancelled
    await expect(page.getByText('Cancelled')).toBeVisible()
  })
})
```

### Error Handling Tests

```typescript
test.describe('Error Handling', () => {
  test('should show validation errors', async ({ page }) => {
    await page.goto('/delivery-requests/create')

    // Submit empty form
    await page.click('button[type="submit"]')

    // Check validation messages
    await expect(page.getByText(/pickup.*required/i)).toBeVisible()
    await expect(page.getByText(/delivery.*required/i)).toBeVisible()
  })

  test('should handle server errors gracefully', async ({ page }) => {
    await page.goto('/login')

    // Fill form
    await page.fill('input[name="email"]', 'test@example.com')
    await page.fill('input[name="password"]', 'password')

    // Intercept API error
    await page.route('**/api/v1/auth/login', route => {
      route.abort('failed')
    })

    await page.click('button[type="submit"]')

    // Check error message
    await expect(page.getByText(/error|network|offline/i)).toBeVisible()
  })

  test('should show timeout error', async ({ page }) => {
    await page.goto('/delivery-requests')

    // Slow down network
    await page.route('**/*', route => {
      setTimeout(() => route.continue(), 60000)
    })

    await expect(page.getByText(/timeout/i)).toBeVisible({ timeout: 35000 })
  })
})
```

### Responsive Design Tests

```typescript
test.describe('Responsive Design', () => {
  // Test on mobile
  test('should work on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 })

    await page.goto('/dashboard')

    // Menu should be mobile-friendly
    const menu = page.locator('[data-testid="mobile-menu"]')
    await expect(menu).toBeVisible()

    // Navigation should collapse
    await page.click('button[aria-label="Menu"]')
    await expect(page.getByText('Home')).toBeVisible()
  })

  // Test on tablet
  test('should work on tablet', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 })

    await page.goto('/dashboard')
    await expect(page.getByText('Dashboard')).toBeVisible()
  })

  // Test on desktop
  test('should work on desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 })

    await page.goto('/dashboard')
    await expect(page.getByText('Dashboard')).toBeVisible()
  })
})
```

## Best Practices

### 1. Use Semantic Selectors

```typescript
// ✅ Good: accessible, semantic
page.getByRole('button', { name: 'Submit' })
page.getByLabel('Email')
page.getByPlaceholder('Enter email')

// ❌ Avoid: brittle, not semantic
page.locator('div.container > div:nth-child(2) button')
page.locator('#btn123')
```

### 2. Wait for Elements Properly

```typescript
// ✅ Good: wait for visibility
await page.locator('button').waitFor({ state: 'visible' })
await expect(button).toBeVisible()

// ❌ Avoid: hard sleeps
await page.waitForTimeout(2000)  // Don't do this!
```

### 3. Test Behavior, Not Implementation

```typescript
// ✅ Good: test user behavior
await page.click('button[aria-label="Delete"]')
await expect(page.getByText('Item deleted')).toBeVisible()

// ❌ Avoid: test internal state
expect(await page.evaluate(() => store.state.deleted)).toBe(true)
```

### 4. Avoid Test Interdependence

```typescript
// ✅ Good: each test is independent
test.beforeEach(async ({ page }) => {
  // Login before each test
  await login(page)
})

// ❌ Avoid: tests depend on order
test('create order', ...) // creates order
test('verify order', ...) // depends on previous test
```

### 5. Use Page Object Models for Complex Tests

```typescript
// pages/auth.ts
export class AuthPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/login')
  }

  async login(email: string, password: string) {
    await this.page.fill('input[name="email"]', email)
    await this.page.fill('input[name="password"]', password)
    await this.page.click('button[type="submit"]')
    await this.page.waitForURL('/dashboard')
  }
}

// test.spec.ts
test('should login', async ({ page }) => {
  const auth = new AuthPage(page)
  await auth.goto()
  await auth.login('user@example.com', 'password')
  await expect(page).toHaveURL('/dashboard')
})
```

## Debugging

### UI Mode

**Interactive test runner with UI:**

```bash
npm run test:e2e:ui
```

Features:
- See test run step-by-step
- View DOM at each step
- Inspect element selectors
- Pause and resume execution

### Debug Mode

**Opens Playwright Inspector:**

```bash
npm run test:e2e:debug
```

Controls:
- Step over (→): Execute next action
- Step in (↓): Dive deeper
- Continue (▶): Run to next breakpoint
- View console output
- Inspect elements

### Test Report

**View HTML report of test results:**

```bash
npm run test:e2e
npx playwright show-report
```

Shows:
- Test status (pass/fail)
- Execution time
- Screenshots on failure
- Video on failure
- Trace of failed tests

### Debugging Selectors

```typescript
// Use page.inspect to see matched elements
await page.locator('button').first().screenshot()

// Get text of all elements
const buttons = await page.locator('button').allTextContents()
console.log(buttons)

// Check if element matches
const isVisible = await page.locator('button').isVisible()
```

## Handling Flaky Tests

### Retry Logic

CI automatically retries flaky tests:

```yaml
# playwright.config.ts
retries: process.env.CI ? 2 : 0,
```

### Make Tests Deterministic

```typescript
// ✅ Good: wait for element
await page.locator('button').waitFor({ state: 'visible' })
await page.click('button')

// ❌ Avoid: race conditions
await page.click('button') // button might not be rendered yet
```

### Handle Dynamic Content

```typescript
// ✅ Good: wait for specific state
await page.waitForFunction(() => {
  return document.querySelectorAll('li').length > 5
})

// ✅ Good: wait for element to appear
await page.locator('.new-item').waitFor()

// ❌ Avoid: assume timing
setTimeout(() => {
  // might not work consistently
}, 1000)
```

### Network Mocking

```typescript
// Mock API response
await page.route('**/api/v1/delivery-requests', route => {
  route.fulfill({
    status: 200,
    contentType: 'application/json',
    body: JSON.stringify([{ id: 1, status: 'pending' }]),
  })
})

// Abort API call
await page.route('**/api/v1/auth/login', route => {
  route.abort('failed')
})

// Delay API response
await page.route('**/api/v1/**', route => {
  setTimeout(() => route.continue(), 5000)
})
```

## CI Integration

### Running in CI

```yaml
# .github/workflows/playwright.yml
- name: Run Playwright E2E tests
  run: npm run test:e2e
```

**Features:**
- PostgreSQL service container
- Laravel server auto-starts
- Retries: 2x per test
- Screenshots on failure
- Videos on failure
- Test results uploaded to artifacts

### Interpreting CI Results

**Check artifacts:**
1. Go to GitHub Actions run
2. Click "Artifacts" at bottom
3. Download `playwright-report` ZIP
4. Extract and open `index.html` in browser

**View test output:**
1. Click failed job
2. Expand "Run Playwright E2E tests"
3. Scroll to see error details

## Performance Tips

### Parallel Execution

Playwright runs tests in parallel by default (4 workers):

```bash
# Run with custom worker count
npm run test:e2e -- --workers=2
```

### Reduce Test Time

```typescript
// Skip unrelated tests
test.skip('should do something', async ({ page }) => {
  // This test is skipped
})

// Only run specific test
test.only('critical test', async ({ page }) => {
  // Only this test runs
})

// Group related tests
test.describe('Feature', () => {
  test.beforeAll(async () => {
    // Setup once for all tests in group
  })
})
```

## Resources

- [Playwright Documentation](https://playwright.dev)
- [Playwright API Reference](https://playwright.dev/docs/api/class-page)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Test Examples Repository](https://github.com/microsoft/playwright/tree/main/examples)

#!/bin/bash

# E2E Test Runner
#
# Runs Playwright E2E tests with proper setup.
# Usage: ./scripts/run-e2e-tests.sh [options]
#
# Options:
#   --ui              Run in UI mode
#   --debug           Run in debug mode
#   --headed          Run with visible browser windows
#   --project=NAME    Run specific browser (chromium|firefox|webkit)
#   --grep=PATTERN    Run tests matching pattern
#   --no-build        Skip frontend build
#   --no-server       Skip backend server startup (use existing)
#
# Examples:
#   ./scripts/run-e2e-tests.sh                    # Run all tests
#   ./scripts/run-e2e-tests.sh --ui               # Run in UI mode
#   ./scripts/run-e2e-tests.sh --debug            # Debug mode
#   ./scripts/run-e2e-tests.sh --grep=auth        # Run auth tests only

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Options
UI_MODE=false
DEBUG_MODE=false
HEADED_MODE=false
PROJECT=""
GREP=""
SKIP_BUILD=false
SKIP_SERVER=false
PW_ARGS=""

print_step() {
  echo -e "${YELLOW}→ $1${NC}"
}

print_success() {
  echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
  echo -e "${RED}✗ $1${NC}"
}

print_info() {
  echo -e "${BLUE}ℹ $1${NC}"
}

# Parse arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    --ui)
      UI_MODE=true
      shift
      ;;
    --debug)
      DEBUG_MODE=true
      shift
      ;;
    --headed)
      HEADED_MODE=true
      PW_ARGS="$PW_ARGS --headed"
      shift
      ;;
    --project=*)
      PROJECT="${1#*=}"
      PW_ARGS="$PW_ARGS --project=$PROJECT"
      shift
      ;;
    --grep=*)
      GREP="${1#*=}"
      PW_ARGS="$PW_ARGS --grep=$GREP"
      shift
      ;;
    --no-build)
      SKIP_BUILD=true
      shift
      ;;
    --no-server)
      SKIP_SERVER=true
      shift
      ;;
    *)
      PW_ARGS="$PW_ARGS $1"
      shift
      ;;
  esac
done

echo -e "${GREEN}=== E2E Test Runner ===${NC}"
echo ""

# Check prerequisites
print_step "Checking prerequisites..."

if ! command -v node &> /dev/null; then
  print_error "Node.js not found"
  exit 1
fi
print_success "Node.js installed"

if ! command -v npx &> /dev/null; then
  print_error "npx not found"
  exit 1
fi
print_success "npx available"

# Check if Playwright is installed
if [ ! -d "frontend/node_modules/@playwright" ]; then
  print_error "Playwright not installed. Run: cd frontend && npm install"
  exit 1
fi
print_success "Playwright installed"

# Check if backend is ready
if [ "$SKIP_SERVER" = false ]; then
  print_step "Checking backend availability..."
  
  if curl -s http://127.0.0.1:8000/up > /dev/null 2>&1; then
    print_success "Backend is running on http://127.0.0.1:8000"
  else
    print_error "Backend is not running on http://127.0.0.1:8000"
    echo ""
    echo "Start the backend with:"
    echo "  cd backend && php artisan serve --host=127.0.0.1 --port=8000"
    exit 1
  fi
fi

echo ""

# Build frontend if needed
if [ "$SKIP_BUILD" = false ]; then
  print_step "Building frontend..."
  cd frontend
  npm run build
  print_success "Frontend built"
  cd ..
  echo ""
fi

# Run tests
print_step "Running Playwright E2E tests..."
echo ""

cd frontend

if [ "$UI_MODE" = true ]; then
  print_info "Starting UI mode..."
  npx playwright test --ui $PW_ARGS
elif [ "$DEBUG_MODE" = true ]; then
  print_info "Starting debug mode..."
  npx playwright test --debug $PW_ARGS
else
  print_info "Running tests..."
  # Show test output
  npx playwright test $PW_ARGS
  TEST_EXIT=$?
  
  echo ""
  
  if [ $TEST_EXIT -eq 0 ]; then
    print_success "All tests passed!"
  else
    print_error "Some tests failed"
    echo ""
    print_info "View detailed report:"
    echo "  npx playwright show-report"
  fi
  
  exit $TEST_EXIT
fi

cd ..

echo ""
print_success "E2E testing complete"

#!/bin/bash

# CI Setup Script
# 
# Prepares the environment for running tests locally in a way that matches CI.
# Usage: ./scripts/ci-setup.sh
#
# This script:
# 1. Creates/refreshes PostgreSQL test database
# 2. Installs dependencies (Composer, npm)
# 3. Sets up environment variables
# 4. Runs migrations
# 5. Optionally seeds test data

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== CI Setup Script ===${NC}"
echo ""

# Colors for output
print_step() {
  echo -e "${YELLOW}→ $1${NC}"
}

print_success() {
  echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
  echo -e "${RED}✗ $1${NC}"
}

# Check prerequisites
print_step "Checking prerequisites..."

if ! command -v php &> /dev/null; then
  print_error "PHP not installed"
  exit 1
fi
print_success "PHP $(php --version | head -n 1)"

if ! command -v composer &> /dev/null; then
  print_error "Composer not installed"
  exit 1
fi
print_success "Composer installed"

if ! command -v node &> /dev/null; then
  print_error "Node.js not installed"
  exit 1
fi
print_success "Node.js $(node --version)"

if ! command -v npm &> /dev/null; then
  print_error "npm not installed"
  exit 1
fi
print_success "npm $(npm --version)"

if ! command -v psql &> /dev/null; then
  print_error "PostgreSQL not installed"
  exit 1
fi
print_success "PostgreSQL installed"

echo ""

# Check if PostgreSQL is running
print_step "Checking PostgreSQL server..."

if pg_isready -h 127.0.0.1 -p 5432 -U app_gm_user &> /dev/null; then
  print_success "PostgreSQL is running"
else
  print_error "PostgreSQL is not running or user not found"
  echo "Start PostgreSQL with: sudo systemctl start postgresql (Linux) or brew services start postgresql@16 (macOS)"
  exit 1
fi

echo ""

# Setup test database
print_step "Setting up test database..."

# Check if database exists
if psql -h 127.0.0.1 -U app_gm_user -lqt | cut -d \| -f 1 | grep -qw maldideliv_test; then
  print_success "Test database 'maldideliv_test' exists"
  
  # Drop and recreate for fresh state
  echo -n "Drop and recreate test database? (y/n) "
  read -r response
  if [[ "$response" =~ ^[Yy]$ ]]; then
    PGPASSWORD="sx3!asvc!asd12" dropdb -h 127.0.0.1 -U app_gm_user maldideliv_test 2>/dev/null || true
    PGPASSWORD="sx3!asvc!asd12" createdb -h 127.0.0.1 -U app_gm_user maldideliv_test
    print_success "Test database recreated"
  fi
else
  print_step "Creating test database..."
  PGPASSWORD="sx3!asvc!asd12" createdb -h 127.0.0.1 -U app_gm_user maldideliv_test
  print_success "Test database created"
fi

echo ""

# Backend setup
print_step "Setting up backend..."

cd backend

# Copy .env if not exists
if [ ! -f .env ]; then
  print_step "Creating .env from .env.example..."
  cp .env.example .env
  php artisan key:generate
  print_success ".env created with app key"
fi

# Install dependencies
if [ ! -d vendor ]; then
  print_step "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist
  print_success "Composer dependencies installed"
else
  print_success "Composer dependencies already installed"
fi

# Install npm dependencies
if [ ! -d node_modules ]; then
  print_step "Installing npm dependencies..."
  npm install --ignore-scripts
  print_success "npm dependencies installed"
else
  print_success "npm dependencies already installed"
fi

# Build assets
print_step "Building assets..."
npm run build
print_success "Assets built"

# Run migrations
print_step "Running migrations..."
php artisan migrate:refresh --force
print_success "Migrations completed"

cd ..

echo ""

# Frontend setup
print_step "Setting up frontend..."

cd frontend

if [ ! -d node_modules ]; then
  print_step "Installing npm dependencies..."
  npm ci
  print_success "npm dependencies installed"
else
  print_success "npm dependencies already installed"
fi

cd ..

echo ""

# Optional: Seed test data
print_step "Seed test data? (y/n)"
read -r response
if [[ "$response" =~ ^[Yy]$ ]]; then
  cd backend
  php artisan db:seed --force
  print_success "Database seeded with test data"
  cd ..
fi

echo ""
echo -e "${GREEN}=== Setup Complete ===${NC}"
echo ""
echo "You can now run tests:"
echo ""
echo "  Backend tests:"
echo "    cd backend && php artisan test"
echo "    cd backend && php artisan test --testsuite=Unit"
echo "    cd backend && php artisan test --testsuite=Feature"
echo ""
echo "  Frontend tests:"
echo "    cd frontend && npm run test:unit"
echo ""
echo "  E2E tests (requires backend running):"
echo "    cd backend && php artisan serve --port=8000"
echo "    cd frontend && npm run test:e2e"
echo ""

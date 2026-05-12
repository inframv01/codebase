# Railway Deployment Guide

This repository has two independent apps:

- Backend API: Laravel in backend/
- Frontend SPA: React + Vite in frontend/

Recommended Railway setup uses one project with four services:

1. PostgreSQL service
2. Backend web service (Laravel API)
3. Backend worker service (Laravel queues)
4. Frontend static service (Vite build output)

## 1. Create Railway Services

Create one Railway project, connect this GitHub repository, then add services:

1. PostgreSQL
2. backend-web (from this repo)
3. backend-worker (from this repo)
4. frontend (from this repo)

For each app service, set the source root directory:

- backend-web: backend/
- backend-worker: backend/
- frontend: frontend/

## 2. Backend Web Service Configuration

Set the service build command:

composer install --no-dev --optimize-autoloader --no-interaction && npm ci && npm run build

Set the service start command:

composer run railway:web

Set the service deploy/release command:

composer run railway:release

### Required Environment Variables

Set these in backend-web service:

- APP_ENV=production
- APP_DEBUG=false
- APP_URL=https://YOUR_BACKEND_RAILWAY_DOMAIN
- APP_KEY=base64:... (generate once and keep stable)
- LOG_CHANNEL=stack
- LOG_LEVEL=info
- DB_CONNECTION=pgsql
- DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD from Railway PostgreSQL variables
- QUEUE_CONNECTION=database
- CACHE_STORE=database
- SESSION_DRIVER=database
- CORS_ALLOWED_ORIGINS=https://YOUR_FRONTEND_RAILWAY_DOMAIN

Optional variables:

- GOOGLE_CLIENT_ID
- GOOGLE_CLIENT_SECRET
- GOOGLE_REDIRECT_URI=https://YOUR_BACKEND_RAILWAY_DOMAIN/api/v1/auth/google/callback
- MAIL_MAILER and SMTP/provider credentials
- UPLOADS_* variables when switching to S3-compatible object storage

## 3. Backend Worker Service Configuration

Set the service build command:

composer install --no-dev --optimize-autoloader --no-interaction && npm ci && npm run build

Set the service start command:

composer run railway:worker

Use the same environment variables as backend-web, including database and queue settings.

Do not set a public domain for backend-worker.

## 4. Frontend Service Configuration (Static)

Set the service build command:

npm ci && npm run build

Set output directory to:

dist

Set environment variables:

- VITE_API_URL=https://YOUR_BACKEND_RAILWAY_DOMAIN/api/v1

Because the frontend uses BrowserRouter, configure SPA fallback in Railway static hosting so unknown paths resolve to index.html.

## 5. Database Setup and Migrations

The backend release command runs migrations automatically:

- php artisan migrate --force

This must run on each deploy for schema changes.

## 6. First Deploy Verification Checklist

1. Backend health endpoint works:

- GET https://YOUR_BACKEND_RAILWAY_DOMAIN/up

2. API base works:

- GET https://YOUR_BACKEND_RAILWAY_DOMAIN/api/v1/lookups/atolls

3. Frontend loads and calls API without CORS errors.
4. Worker logs show queue processing when an action dispatches a queued job.

## 7. Scaling Notes

- Start with one backend worker instance.
- Increase worker replicas if queue latency grows.
- Keep backend-web and backend-worker on the same code revision.

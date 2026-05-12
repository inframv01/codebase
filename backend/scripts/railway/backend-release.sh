#!/usr/bin/env bash

set -euo pipefail

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan migrate --force

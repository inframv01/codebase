#!/usr/bin/env bash

set -euo pipefail

php artisan queue:work --tries=1 --timeout=90

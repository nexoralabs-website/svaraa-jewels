#!/usr/bin/env bash
# Svaraa Jewels — Zero-downtime deployment script
# Usage: bash deployment/deploy.sh

set -euo pipefail

APP_DIR="/var/www/svaraa-jewels"
cd "$APP_DIR"

echo "==> [1/9] Pulling latest code..."
git pull origin main

echo "==> [2/9] Installing PHP dependencies (production)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> [3/9] Installing Node dependencies & building assets..."
npm ci
npm run build

echo "==> [4/9] Running migrations..."
php artisan migrate --force

echo "==> [5/9] Clearing caches..."
php artisan optimize:clear

echo "==> [6/9] Caching config + routes + views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> [7/9] Syncing storage link..."
php artisan storage:link --force 2>/dev/null || true

echo "==> [8/9] Restarting queue workers..."
php artisan queue:restart

echo "==> [9/9] Reloading Supervisor..."
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart all

echo ""
echo "✅ Deployment complete."

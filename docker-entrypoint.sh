#!/usr/bin/env bash
set -e

# Function to log messages with timestamp
log() {
  echo "[$(date +"%Y-%m-%d %H:%M:%S")] $*"
}

# 1. Environment validation (Fail-fast)
if [ ! -f .env ]; then
  log "WARNING: .env file is missing. Validating system environment variables..."
  if [ -z "$APP_KEY" ] || [ -z "$DB_PASSWORD" ]; then
    log "ERROR: Configuration is incomplete! Both .env file and required environment variables (APP_KEY, DB_PASSWORD) are missing."
    exit 1
  fi
else
  IF_ENV_APP_KEY="$APP_KEY"
  if [ -z "$IF_ENV_APP_KEY" ]; then
    IF_ENV_APP_KEY=$(grep -E "^APP_KEY=" .env | cut -d '=' -f2- | tr -d '"'\'' ')
  fi

  if [ -z "$IF_ENV_APP_KEY" ] || [ "$IF_ENV_APP_KEY" = "base64:" ]; then
    log "ERROR: APP_KEY is missing or invalid! The container must be provided with a secure APP_KEY in the environment or .env file."
    exit 1
  fi
fi

# 2. Wait for database to become reachable
CHECK_DB_CONN="$DB_CONNECTION"
CHECK_DB_HOST="$DB_HOST"
CHECK_DB_PORT="$DB_PORT"
CHECK_DB_USER="$DB_USERNAME"
CHECK_DB_PASS="$DB_PASSWORD"

if [ -f .env ]; then
  [ -z "$CHECK_DB_CONN" ] && CHECK_DB_CONN=$(grep -E '^DB_CONNECTION=' .env | cut -d '=' -f2- | tr -d '"'\'' ')
  [ -z "$CHECK_DB_HOST" ] && CHECK_DB_HOST=$(grep -E '^DB_HOST=' .env | cut -d '=' -f2- | tr -d '"'\'' ')
  [ -z "$CHECK_DB_PORT" ] && CHECK_DB_PORT=$(grep -E '^DB_PORT=' .env | cut -d '=' -f2- | tr -d '"'\'' ')
  [ -z "$CHECK_DB_USER" ] && CHECK_DB_USER=$(grep -E '^DB_USERNAME=' .env | cut -d '=' -f2- | tr -d '"'\'' ')
  [ -z "$CHECK_DB_PASS" ] && CHECK_DB_PASS=$(grep -E '^DB_PASSWORD=' .env | cut -d '=' -f2- | tr -d '"'\'' ')
fi

CHECK_DB_CONN="${CHECK_DB_CONN:-mysql}"
CHECK_DB_HOST="${CHECK_DB_HOST:-127.0.0.1}"

if [ "$CHECK_DB_CONN" = "pgsql" ]; then
  CHECK_DB_PORT="${CHECK_DB_PORT:-5432}"
  log "Waiting for PostgreSQL at ${CHECK_DB_HOST}:${CHECK_DB_PORT}..."
  export PGPASSWORD="${CHECK_DB_PASS}"

  until pg_isready \
      -h "${CHECK_DB_HOST}" \
      -p "${CHECK_DB_PORT}" \
      -U "${CHECK_DB_USER}"
  do
      log "Waiting for PostgreSQL..."
      sleep 2
  done

  log "PostgreSQL is up."
else
  CHECK_DB_PORT="${CHECK_DB_PORT:-3306}"
  log "Waiting for MySQL at ${CHECK_DB_HOST}:${CHECK_DB_PORT}..."
  while ! mysqladmin ping \
      -h"${CHECK_DB_HOST}" \
      -P"${CHECK_DB_PORT}" \
      -u"${CHECK_DB_USER}" \
      -p"${CHECK_DB_PASS}" \
      --skip-ssl \
      --silent
  do
      log "Waiting for MySQL..."
      sleep 2
  done

  log "MySQL is up."
fi

# 3. Optional Database Migrations
if [ "$RUN_MIGRATIONS" = "true" ]; then
  log "RUN_MIGRATIONS is set to true. Running database migrations..."
  php artisan migrate --force
else
  log "RUN_MIGRATIONS is not set to true. Skipping database migrations."
fi

# 4. Install Composer dependencies if vendor folder is missing (fallback only)
if [ ! -d "vendor" ]; then
  log "Vendor directory missing. Running composer install..."
  composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction
else
  log "Vendor directory exists. Skipping composer install."
fi

# 5. Ensure storage symlink exists
log "Ensuring storage symlink..."
php artisan storage:link || true

# 6. Set proper permissions (already set in Dockerfile, but ensure at runtime)
log "Setting permissions on storage, bootstrap/cache, public..."
chown -R www-data:www-data storage bootstrap/cache public
chmod -R 775 storage bootstrap/cache public

# 7. Cache configuration and routes for performance
log "Caching configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true

# 8. Execute the final command (Apache foreground)
log "Starting Apache..."
exec "$@"

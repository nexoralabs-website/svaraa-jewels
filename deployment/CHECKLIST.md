# Svaraa Jewels — Production Deployment Checklist

## Pre-deployment
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false` in `.env`
- [ ] Generate app key: `php artisan key:generate`
- [ ] Configure `DB_*` for MySQL/PostgreSQL (not SQLite)
- [ ] Set `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`
- [ ] Set all `RAZORPAY_*` live keys
- [ ] Set `MEILISEARCH_HOST` and `MEILISEARCH_KEY`
- [ ] Set `MAIL_*` to production SMTP
- [ ] Set `SESSION_SECURE_COOKIE=true` and `SESSION_ENCRYPT=true`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan storage:link`

## Queue & Scheduler
- [ ] Install Supervisor: `apt install supervisor`
- [ ] Copy `deployment/supervisor-queue.conf` to `/etc/supervisor/conf.d/`
- [ ] Run `supervisorctl update && supervisorctl start all`
- [ ] Add cron: `* * * * * www-data php /var/www/svaraa-jewels/artisan schedule:run >> /dev/null 2>&1`

## Meilisearch
- [ ] Start Meilisearch: `meilisearch --master-key=yourkey`
- [ ] Index products: `php artisan scout:import "App\Models\Product"`

## SSL & Web Server
- [ ] Nginx/Apache configured with SSL (Let's Encrypt)
- [ ] `Strict-Transport-Security` header active (SecurityHeaders middleware handles this in production)

## Post-deployment verification
- [ ] Visit `/up` → should return `200 OK`
- [ ] Add item to cart → verify count updates
- [ ] Search "ring" → autocomplete shows products in ₹
- [ ] Apply coupon → discount row appears
- [ ] Place COD order → success page shows with correct order number
- [ ] Admin panel `/admin` → accessible with Filament user
- [ ] Invoice download → PDF generates correctly

## Rollback
```bash
git revert HEAD --no-edit
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback  # if needed
php artisan optimize:clear
php artisan config:cache && php artisan route:cache
php artisan queue:restart
```

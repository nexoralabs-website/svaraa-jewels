# Production Deployment Guide

## Environment Checklist

```env
APP_NAME="Svaraa Jewels"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://svaraa.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=svaraa_production
DB_USERNAME=svaraa_user
DB_PASSWORD=secure_password

# Redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@svaraa.com
MAIL_PASSWORD=mailgun_password
MAIL_FROM=orders@svaraa.com
MAIL_FROM_NAME="Svaraa Jewels"

# Razorpay
RAZORPAY_KEY=your_live_key
RAZORPAY_SECRET=your_live_secret
RAZORPAY_WEBHOOK_SECRET=webhook_secret

# Payment Alerts
PAYMENT_ALERT_EMAIL=ops@svaraa.com
PAYMENT_ALERT_WEBHOOK_FAILURES=5
PAYMENT_ALERT_RECONCILIATION_FAILURES=3
PAYMENT_ALERT_STOCK_CONFLICTS=1
PAYMENT_ALERT_QUEUE_BACKLOG=50
PAYMENT_ALERT_FAILED_JOBS=1
PAYMENT_ALERT_CAPTURE_SUCCESS_RATE_MIN=90

# Cache
CACHE_STORE=redis
SESSION_DRIVER=redis
```

## Queue Worker Setup

### Using Supervisor

```ini
[program:svaraa-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/svaraa-jewels/artisan queue:work redis --queue=emails,default --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/svaraa-jewels/storage/logs/queue-worker.log
stopwaitsecs=3600

[program:svaraa-scheduler]
command=php /var/www/svaraa-jewels/artisan schedule:run --no-interaction
directory=/var/www/svaraa-jewels
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/www/svaraa-jewels/storage/logs/scheduler.log
```

## Cron Setup

```cron
* * * * * cd /var/www/svaraa-jewels && php artisan schedule:run >> /dev/null 2>&1
```

## SSL Setup (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d svaraa.com -d www.svaraa.com
sudo certbot renew --dry-run
```

## Storage Permissions

```bash
sudo chown -R www-data:www-data /var/www/svaraa-jewels/storage
sudo chmod -R 775 /var/www/svaraa-jewels/storage
sudo chown -R www-data:www-data /var/www/svaraa-jewels/bootstrap/cache
sudo chmod -R 775 /var/www/svaraa-jewels/bootstrap/cache
```

## Rollback Plan

1. Backup current release:
```bash
cp -r /var/www/svaraa-jewels /var/www/svaraa-jewels-backup-$(date +%Y%m%d)
```

2. Restore from backup:
```bash
rm -rf /var/www/svaraa-jewels
cp -r /var/www/svaraa-jewels-backup-YYYYMMDD /var/www/svaraa-jewels
```

3. Restore database:
```bash
mysql -u svaraa_user -p svaraa_production < backup.sql
```
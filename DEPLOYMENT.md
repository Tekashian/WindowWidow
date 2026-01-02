# Production Deployment Guide - Window Factory

This guide covers deploying the Window Factory system to a production environment.

## Pre-Deployment Checklist

### Backend Requirements
- [ ] PHP 8.1+ installed
- [ ] Composer installed
- [ ] MySQL 8.0+ database created
- [ ] Web server configured (Apache/Nginx)
- [ ] SSL certificate installed
- [ ] Required PHP extensions enabled

### Frontend Requirements
- [ ] Node.js 18+ installed
- [ ] npm or yarn package manager
- [ ] Production domain configured
- [ ] CDN configured (optional)

## Backend Deployment

### 1. Server Configuration

#### Nginx Configuration Example
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.windowfactory.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.windowfactory.com;
    
    root /var/www/window-factory/backend/public;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 2. Deploy Backend Code

```bash
# Clone repository
git clone https://github.com/your-org/window-factory.git
cd window-factory/backend

# Install dependencies (production mode)
composer install --optimize-autoloader --no-dev

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Configure Environment

Create production `.env` file:

```env
APP_NAME="Window Factory API"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.windowfactory.com

FRONTEND_URL=https://app.windowfactory.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=window_factory_prod
DB_USERNAME=window_factory_user
DB_PASSWORD=SECURE_PASSWORD_HERE

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@windowfactory.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Run Deployment Commands

```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed database (only on first deployment)
php artisan db:seed --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Link storage
php artisan storage:link

# Optimize autoloader
composer dump-autoload --optimize
```

### 5. Configure Queue Worker

Create systemd service `/etc/systemd/system/window-factory-worker.service`:

```ini
[Unit]
Description=Window Factory Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/window-factory/backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Enable and start the service:
```bash
sudo systemctl enable window-factory-worker
sudo systemctl start window-factory-worker
```

### 6. Configure Scheduler

Add to crontab (`sudo crontab -e -u www-data`):
```
* * * * * cd /var/www/window-factory/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Frontend Deployment

### 1. Build for Production

```bash
cd frontend

# Install dependencies
npm ci

# Build production bundle
npm run build
```

### 2. Deploy Static Files

#### Option A: Traditional Web Server

**Nginx Configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name app.windowfactory.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name app.windowfactory.com;
    
    root /var/www/window-factory/frontend/dist;
    index index.html;
    
    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;
    
    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # SPA routing
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 10240;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;
}
```

Copy files:
```bash
sudo cp -r dist/* /var/www/window-factory/frontend/dist/
sudo chown -R www-data:www-data /var/www/window-factory/frontend/dist
```

#### Option B: CDN Deployment (e.g., Cloudflare)

```bash
# Upload dist folder to CDN
aws s3 sync dist/ s3://your-bucket-name/ --delete
# Or use your CDN's CLI tool
```

### 3. Environment Configuration

Create production environment file `.env.production`:

```env
VITE_API_URL=https://api.windowfactory.com/api
```

Rebuild with production settings:
```bash
npm run build
```

## Database Backup Strategy

### Automated Backups

Create backup script `/usr/local/bin/backup-window-factory.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/window-factory"
DB_NAME="window_factory_prod"
DB_USER="window_factory_user"
DB_PASS="YOUR_PASSWORD"

# Create backup directory
mkdir -p $BACKUP_DIR

# Dump database
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

# Upload to S3 (optional)
aws s3 cp $BACKUP_DIR/db_$DATE.sql.gz s3://your-backup-bucket/
```

Schedule daily backups:
```bash
sudo crontab -e
# Add:
0 2 * * * /usr/local/bin/backup-window-factory.sh
```

## Monitoring and Logging

### Application Monitoring

Configure Laravel Telescope (development) or Laravel Horizon (production):

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

### Error Tracking

Integrate with Sentry or similar:

```bash
composer require sentry/sentry-laravel
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

Configure in `.env`:
```env
SENTRY_LARAVEL_DSN=your-sentry-dsn
SENTRY_TRACES_SAMPLE_RATE=0.2
```

### Log Rotation

Configure logrotate `/etc/logrotate.d/window-factory`:

```
/var/www/window-factory/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

## SSL Certificate

### Using Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d api.windowfactory.com -d app.windowfactory.com
sudo certbot renew --dry-run
```

## Performance Optimization

### Enable OPcache

In `/etc/php/8.1/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### Configure Redis

```bash
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### Enable HTTP/2

Ensure your Nginx/Apache configuration includes HTTP/2 support.

## Security Hardening

### Firewall Configuration

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Disable Dangerous PHP Functions

In `php.ini`:
```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

### Regular Updates

```bash
# Create update script
sudo apt update && sudo apt upgrade -y
composer update --no-dev
npm update
```

## Rollback Procedure

If deployment fails:

```bash
# Backend
cd /var/www/window-factory/backend
git checkout previous-stable-tag
composer install --no-dev
php artisan migrate:rollback
php artisan config:cache
sudo systemctl restart php8.1-fpm

# Frontend
cd /var/www/window-factory/frontend
git checkout previous-stable-tag
npm ci
npm run build
sudo cp -r dist/* /var/www/window-factory/frontend/dist/
```

## Health Checks

Create health check endpoint monitoring:

```bash
# Monitor API
curl https://api.windowfactory.com/api/health

# Monitor Frontend
curl https://app.windowfactory.com
```

Set up automated health checks with UptimeRobot or similar service.

## Post-Deployment Verification

- [ ] Login functionality works
- [ ] All API endpoints responding
- [ ] Dashboard loads correctly
- [ ] Database connections stable
- [ ] SSL certificates valid
- [ ] Email notifications working
- [ ] Queue workers running
- [ ] Scheduled tasks executing
- [ ] Error logs clean
- [ ] Performance acceptable

## Support and Maintenance

### Regular Maintenance Tasks

**Daily:**
- Check error logs
- Monitor application performance
- Verify backups completed

**Weekly:**
- Review security logs
- Update dependencies (if needed)
- Check disk space

**Monthly:**
- Security audit
- Performance optimization
- Database optimization
- Backup restoration test

---

**Deployment Version:** 1.0.0  
**Last Updated:** January 2026

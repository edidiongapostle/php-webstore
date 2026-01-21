# Deployment Guide

This guide covers deploying WebStore to production environments.

## 🚀 Quick Deployment

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/webstore.git
cd webstore
```

### 2. Setup Configuration
```bash
# Copy example configuration
cp config.example.php config.php

# Edit configuration for your environment
nano config.php
```

### 3. Initialize Database
```bash
# Create database and tables
php database/init.php

# Set proper permissions
chmod 755 database/
chmod 666 database/webstore.db
```

### 4. Configure Web Server

#### Apache (.htaccess)
```apache
# Enable PHP and set directory index
DirectoryIndex index.php

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# URL rewriting
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/webstore;
    index index.php;

    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";

    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 5. Set Permissions
```bash
# Web server permissions
chown -R www-data:www-data /var/www/webstore
chmod -R 755 /var/www/webstore
chmod -R 666 /var/www/webstore/database
```

## 🔒 Security Configuration

### Production Settings in config.php
```php
// Environment
define('ENVIRONMENT', 'production');

// Disable error display
error_reporting(0);
ini_set('display_errors', 0);

// Enforce HTTPS
define('ENFORCE_HTTPS', true);

// Secure session settings
define('SESSION_LIFETIME', 3600); // 1 hour
```

### SSL Certificate Setup
```bash
# Let's Encrypt (recommended)
certbot --nginx -d yourdomain.com

# Or upload your own certificate
# Place cert.pem and key.pem in /etc/ssl/
```

## 🐳 Docker Deployment

### Dockerfile
```dockerfile
FROM php:8.0-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
```

### docker-compose.yml
```yaml
version: '3.8'

services:
  webstore:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./database:/var/www/html/database
    environment:
      - ENVIRONMENT=production
      - SITE_NAME=Your Store Name
      - SITE_EMAIL=admin@yourdomain.com
    restart: unless-stopped

  # Optional: Database backup service
  backup:
    image: alpine:latest
    volumes:
      - ./database:/backup
    command: |
      sh -c "
        while true; do
          tar -czf /backup/backup-$(date +%Y%m%d-%H%M%S).tar.gz -C /backup .
          sleep 86400
        done
      "
```

### Docker Commands
```bash
# Build and run
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

## 🌐 Cloud Platform Deployment

### Heroku
```bash
# Install Heroku CLI
npm install -g heroku

# Login
heroku login

# Create app
heroku create your-webstore

# Set buildpack
heroku buildpacks:set heroku/php

# Deploy
git push heroku main
```

### DigitalOcean App Platform
```yaml
# .do/app.yaml
name: webstore
services:
- name: web
  source_dir: /
  github:
    repo: yourusername/webstore
    branch: main
  run_command: php -S localhost:8080
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs
  http_port: 8080
  routes:
  - path: /
    preserve_path_prefix: false
```

### AWS Elastic Beanstalk
```bash
# Install EB CLI
pip install awsebcli

# Initialize
eb init webstore

# Deploy
eb create production
```

## 📊 Performance Optimization

### Production .htaccess
```apache
# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain text/html text/xml text/css text/javascript application/javascript application/xml+rss
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options nosniff
    Header set X-Frame-Options DENY
    Header set X-XSS-Protection "1; mode=block"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
```

### PHP OPcache
```ini
; In php.ini or .user.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

## 🔍 Monitoring & Logging

### Application Logging
```php
// Add to config.php for production
define('LOG_FILE', __DIR__ . '/logs/app.log');

function logMessage($level, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message\n";
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}
```

### Log Rotation
```bash
# Create log rotation script
cat > /etc/logrotate.d/webstore << EOF
/path/to/webstore/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        /usr/sbin/apachectl graceful
    endscript
}
EOF
```

## 🔄 Backup Strategy

### Automated Backups
```bash
#!/bin/bash
# backup.sh
BACKUP_DIR="/var/backups/webstore"
DB_FILE="/var/www/webstore/database/webstore.db"
DATE=$(date +%Y%m%d-%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
cp $DB_FILE $BACKUP_DIR/webstore-$DATE.db

# Compress old backups
find $BACKUP_DIR -name "*.db" -mtime +7 -exec gzip {} \;

# Keep only last 30 backups
ls -t $BACKUP_DIR/*.db.gz | tail -n +31 | xargs rm -f
```

### Cron Job
```bash
# Add to crontab
crontab -e

# Daily backup at 2 AM
0 2 * * * /path/to/backup.sh
```

## 🚨 Troubleshooting

### Common Deployment Issues

#### Database Permissions Error
```bash
# Check ownership
ls -la database/webstore.db

# Fix permissions
sudo chown www-data:www-data database/webstore.db
sudo chmod 666 database/webstore.db
```

#### Session Issues
```bash
# Check session directory
php -i | grep session.save_path

# Create session directory
mkdir -p /var/lib/php/sessions
chown www-data:www-data /var/lib/php/sessions
chmod 755 /var/lib/php/sessions
```

#### SSL Certificate Issues
```bash
# Test SSL configuration
openssl s_client -connect yourdomain.com:443

# Check certificate expiry
openssl x509 -in /etc/ssl/cert.pem -noout -dates
```

## 📱 Mobile Optimization

### Progressive Web App
```html
<!-- Add to index.php head -->
<meta name="theme-color" content="#4F46E5">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<link rel="manifest" href="/manifest.json">
```

### manifest.json
```json
{
  "name": "WebStore",
  "short_name": "WebStore",
  "description": "Premium websites for sale",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#4F46E5",
  "icons": [
    {
      "src": "/icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    }
  ]
}
```

## 🧪 Testing Deployment

### Staging Environment
```bash
# Create staging branch
git checkout -b staging

# Deploy to staging server
rsync -avz --delete ./ user@staging:/var/www/webstore-staging/

# Run tests
curl -f http://staging.yourdomain.com/health-check
```

### Health Check Endpoint
```php
<?php
// health-check.php
header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'database' => file_exists(__DIR__ . '/database/webstore.db')
];

echo json_encode($health);
?>
```

## 📈 Scaling Considerations

### Database Scaling
- **Read Replicas**: Multiple read-only database copies
- **Connection Pooling**: Reuse database connections
- **Query Optimization**: Add indexes for frequently queried columns

### Caching Strategy
- **Redis**: Session storage and caching
- **CDN**: Static asset delivery
- **Application Cache**: Product and category caching

### Load Balancing
```nginx
upstream webstore_backend {
    server 127.0.0.1:8080;
    server 127.0.0.1:8081;
    server 127.0.0.1:8082;
}

server {
    listen 80;
    server_name yourdomain.com;
    
    location / {
        proxy_pass http://webstore_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## 🔐 Security Checklist

### Pre-Deployment Security
- [ ] Change default admin password
- [ ] Set strong database permissions
- [ ] Configure HTTPS certificate
- [ ] Set security headers
- [ ] Disable error reporting in production
- [ ] Implement rate limiting
- [ ] Set up monitoring
- [ ] Configure backup system
- [ ] Test all payment methods in sandbox
- [ ] Review file upload restrictions

### Post-Deployment Security
- [ ] Run security scan
- [ ] Test for common vulnerabilities
- [ ] Verify SSL configuration
- [ ] Check file permissions
- [ ] Monitor error logs
- [ ] Test backup restoration
- [ ] Performance testing under load

---

**Ready for production deployment! 🚀**

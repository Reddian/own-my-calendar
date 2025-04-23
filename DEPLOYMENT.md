# Own My Calendar - Deployment Instructions

This document provides comprehensive instructions for deploying the Own My Calendar application to a production environment.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Requirements](#server-requirements)
3. [Environment Setup](#environment-setup)
4. [Database Configuration](#database-configuration)
5. [Application Installation](#application-installation)
6. [External Service Configuration](#external-service-configuration)
   - [Stripe Integration](#stripe-integration)
   - [Google Calendar Integration](#google-calendar-integration)
   - [OpenAI Integration](#openai-integration)
7. [Web Server Configuration](#web-server-configuration)
8. [SSL Certificate Setup](#ssl-certificate-setup)
9. [Scheduled Tasks](#scheduled-tasks)
10. [Deployment Checklist](#deployment-checklist)
11. [Troubleshooting](#troubleshooting)

## Prerequisites

Before deploying the application, ensure you have:

- A web server with SSH access
- Domain name configured with DNS pointing to your server
- Access to create databases
- Composer installed globally
- Git installed
- PHP 8.3+ installed
- Node.js 16+ and NPM installed

## Server Requirements

- PHP >= 8.3
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- MySQL 5.7+ or MariaDB 10.3+
- Nginx or Apache web server
- Minimum 1GB RAM (2GB recommended)
- 20GB disk space

## Environment Setup

1. **Create a production environment file**:

   ```bash
   cp .env.example .env
   ```

2. **Update the environment variables**:

   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=own_my_calendar
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   
   MAIL_MAILER=smtp
   MAIL_HOST=your_mail_host
   MAIL_PORT=587
   MAIL_USERNAME=your_mail_username
   MAIL_PASSWORD=your_mail_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@yourdomain.com
   MAIL_FROM_NAME="${APP_NAME}"
   
   STRIPE_KEY=your_stripe_public_key
   STRIPE_SECRET=your_stripe_secret_key
   STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
   STRIPE_MONTHLY_PRICE_ID=your_stripe_monthly_price_id
   STRIPE_YEARLY_PRICE_ID=your_stripe_yearly_price_id
   
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=https://yourdomain.com/google/callback
   GOOGLE_APPLICATION_CREDENTIALS=/path/to/your/credentials.json
   
   OPENAI_API_KEY=your_openai_api_key
   ```

3. **Generate application key**:

   ```bash
   php artisan key:generate
   ```

## Database Configuration

1. **Create a new database**:

   ```sql
   CREATE DATABASE own_my_calendar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'own_my_calendar'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON own_my_calendar.* TO 'own_my_calendar'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Run database migrations**:

   ```bash
   php artisan migrate
   ```

## Application Installation

1. **Clone the repository**:

   ```bash
   git clone https://github.com/yourusername/own-my-calendar.git /var/www/own-my-calendar
   cd /var/www/own-my-calendar
   ```

2. **Install PHP dependencies**:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Install Node.js dependencies and build assets**:

   ```bash
   npm install
   npm run build
   ```

4. **Set proper permissions**:

   ```bash
   chown -R www-data:www-data /var/www/own-my-calendar
   chmod -R 755 /var/www/own-my-calendar
   chmod -R 775 /var/www/own-my-calendar/storage
   chmod -R 775 /var/www/own-my-calendar/bootstrap/cache
   ```

5. **Cache configuration and routes for better performance**:

   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## External Service Configuration

### Stripe Integration

1. **Create a Stripe account** at [stripe.com](https://stripe.com) if you don't have one.

2. **Create products and pricing plans**:
   - Create a monthly subscription product ($9/month)
   - Create a yearly subscription product ($89/year)
   - Note the price IDs for both plans

3. **Configure webhook endpoint**:
   - In your Stripe Dashboard, go to Developers > Webhooks
   - Add an endpoint: `https://yourdomain.com/stripe/webhook`
   - Select events to listen for:
     - `checkout.session.completed`
     - `customer.subscription.updated`
     - `customer.subscription.deleted`
   - Note the webhook signing secret

4. **Update your .env file** with the Stripe credentials:
   ```
   STRIPE_KEY=your_stripe_public_key
   STRIPE_SECRET=your_stripe_secret_key
   STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
   STRIPE_MONTHLY_PRICE_ID=your_stripe_monthly_price_id
   STRIPE_YEARLY_PRICE_ID=your_stripe_yearly_price_id
   ```

### Google Calendar Integration

1. **Create a Google Cloud project**:
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project
   - Enable the Google Calendar API

2. **Configure OAuth consent screen**:
   - Set up the OAuth consent screen with required information
   - Add the necessary scopes for Google Calendar access

3. **Create OAuth credentials**:
   - Create OAuth 2.0 Client ID credentials
   - Add authorized redirect URIs: `https://yourdomain.com/google/callback`
   - Download the credentials JSON file

4. **Set up credentials on your server**:
   ```bash
   mkdir -p /var/www/own-my-calendar/storage/app/google-calendar
   ```

5. **Upload the credentials.json file** to `/var/www/own-my-calendar/storage/app/google-calendar/credentials.json`

6. **Update your .env file** with Google credentials:
   ```
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=https://yourdomain.com/google/callback
   GOOGLE_APPLICATION_CREDENTIALS=/var/www/own-my-calendar/storage/app/google-calendar/credentials.json
   ```

### OpenAI Integration

1. **Create an OpenAI account** at [openai.com](https://openai.com) if you don't have one.

2. **Generate an API key** in your OpenAI dashboard.

3. **Update your .env file** with the OpenAI API key:
   ```
   OPENAI_API_KEY=your_openai_api_key
   ```

## Web Server Configuration

### Nginx Configuration

Create a new Nginx server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    root /var/www/own-my-calendar/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    location ~ /.well-known {
        allow all;
    }
    
    client_max_body_size 100M;
}
```

### Apache Configuration

Create a new Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    DocumentRoot /var/www/own-my-calendar/public
    
    <Directory /var/www/own-my-calendar/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/own-my-calendar-error.log
    CustomLog ${APACHE_LOG_DIR}/own-my-calendar-access.log combined
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
</VirtualHost>
```

## SSL Certificate Setup

1. **Install Certbot**:

   ```bash
   apt-get update
   apt-get install certbot
   ```

2. **For Nginx**:

   ```bash
   apt-get install python3-certbot-nginx
   certbot --nginx -d yourdomain.com -d www.yourdomain.com
   ```

3. **For Apache**:

   ```bash
   apt-get install python3-certbot-apache
   certbot --apache -d yourdomain.com -d www.yourdomain.com
   ```

4. **Set up auto-renewal**:

   ```bash
   echo "0 3 * * * certbot renew --quiet" | crontab -
   ```

## Scheduled Tasks

Set up Laravel's scheduler to run every minute:

```bash
* * * * * cd /var/www/own-my-calendar && php artisan schedule:run >> /dev/null 2>&1
```

## Deployment Checklist

Before going live, ensure you've completed the following:

- [ ] Environment is set to production
- [ ] Debug mode is turned off
- [ ] Application key is generated
- [ ] Database migrations are run
- [ ] External services are configured (Stripe, Google Calendar, OpenAI)
- [ ] SSL certificate is installed and working
- [ ] File permissions are set correctly
- [ ] Scheduled tasks are configured
- [ ] Configuration, route, and view caching is enabled
- [ ] Error logging is configured
- [ ] Backup system is in place

## Troubleshooting

### Common Issues

1. **500 Server Error**:
   - Check the Laravel log file: `/var/www/own-my-calendar/storage/logs/laravel.log`
   - Check the web server error log

2. **Permission Issues**:
   - Ensure proper ownership: `chown -R www-data:www-data /var/www/own-my-calendar`
   - Ensure proper permissions for storage and cache: `chmod -R 775 /var/www/own-my-calendar/storage /var/www/own-my-calendar/bootstrap/cache`

3. **Database Connection Issues**:
   - Verify database credentials in .env file
   - Ensure the database server is running
   - Check if the database user has proper permissions

4. **Google Calendar Integration Issues**:
   - Verify redirect URIs match exactly in Google Cloud Console and application
   - Check that the credentials.json file exists and is accessible
   - Ensure the Google Calendar API is enabled in Google Cloud Console

5. **Stripe Integration Issues**:
   - Verify Stripe API keys are correct
   - Ensure webhook endpoint is properly configured
   - Check Stripe dashboard for webhook delivery failures

For additional support, please refer to the Laravel documentation or contact the development team.

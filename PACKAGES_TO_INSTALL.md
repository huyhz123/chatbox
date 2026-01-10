# Required Packages for Full Integration

This document lists all additional packages that need to be installed for complete functionality.

## 📦 Composer Packages (PHP)

### Essential Packages

```bash
# Agora.io Official SDK (Required for production)
composer require agora/rtc-token-builder

# For mobile apps, also install RTM token builder
composer require agora/rtm-token-builder
```

### File Upload & Processing

```bash
# Media metadata extraction (highly recommended)
composer require james-heinrich/getid3

# AWS S3 support (if using cloud storage)
composer require league/flysystem-aws-s3-v3

# Image manipulation (optional)
composer require intervention/image
```

### Social OAuth

```bash
# Laravel Socialite (already included in Laravel 11)
# But ensure these drivers are available:

# Google OAuth
composer require laravel/socialite

# Facebook OAuth (included in Socialite)
# Apple OAuth (included in Socialite)
```

### Payment Gateways

```bash
# VNPay, MoMo, ZaloPay integration
# Custom implementation already in PaymentController
# No additional packages required

# Stripe (optional)
composer require stripe/stripe-php

# PayPal (optional)
composer require paypal/rest-api-sdk-php
```

### Push Notifications

```bash
# Firebase Cloud Messaging
composer require kreait/firebase-php

# Or use alternative:
composer require edujugon/push-notification
```

### Additional Utilities

```bash
# Excel export (for reports)
composer require maatwebsite/excel

# PDF generation
composer require barryvdh/laravel-dompdf

# Sitemap generation
composer require spatie/laravel-sitemap

# API rate limiting
composer require spatie/laravel-rate-limiting-middleware
```

---

## 📦 NPM Packages (JavaScript)

### Essential Packages

```bash
# Already installed in package.json:
npm install

# Verify these are present:
# - vue@^3.4
# - @vitejs/plugin-vue
# - laravel-echo
# - pusher-js
# - axios
# - pinia
# - vue-router
# - vue-i18n
# - tailwindcss
# - daisyui
```

### Additional Frontend Libraries

```bash
# Agora Web SDK
npm install agora-rtc-sdk-ng
npm install agora-rtm-sdk

# Video player
npm install video.js
npm install @videojs/http-streaming

# Audio recording
npm install recordrtc

# Image cropper
npm install vue-advanced-cropper

# Chart visualization
npm install chart.js vue-chartjs

# Date/time utilities
npm install dayjs

# Markdown support
npm install marked

# Emoji picker
npm install emoji-mart-vue

# QR code generation
npm install qrcode.vue

# File upload progress
npm install vue-upload-component

# Notifications
npm install vue-toastification
```

### WebRTC Libraries

```bash
# If not using Agora exclusively
npm install simple-peer

# Screen sharing
npm install @github/webrtc-adapter
```

---

## 🔧 System Requirements

### Ubuntu/Debian

```bash
# Update package list
sudo apt-get update

# PHP 8.2 and extensions
sudo apt-get install php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
  php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-redis \
  php8.2-bcmath php8.2-gd php8.2-intl

# MySQL 8.0
sudo apt-get install mysql-server-8.0

# Redis
sudo apt-get install redis-server

# FFmpeg for video processing
sudo apt-get install ffmpeg ffprobe

# ImageMagick (optional)
sudo apt-get install imagemagick

# Supervisor for queue workers
sudo apt-get install supervisor

# Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install nodejs

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### macOS

```bash
# Install Homebrew first
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# PHP 8.2
brew install php@8.2

# MySQL 8.0
brew install mysql@8.0

# Redis
brew install redis

# FFmpeg
brew install ffmpeg

# Node.js
brew install node@18

# Composer
brew install composer
```

---

## 🚀 Installation Order

### 1. Install System Requirements

Follow the system requirements section above for your OS.

### 2. Install Composer Packages

```bash
# Essential packages
composer require agora/rtc-token-builder
composer require james-heinrich/getid3

# Optional packages (choose based on needs)
composer require kreait/firebase-php
composer require intervention/image
```

### 3. Install NPM Packages

```bash
# Base installation
npm install

# Agora SDK
npm install agora-rtc-sdk-ng agora-rtm-sdk

# Additional frontend libraries (optional)
npm install video.js recordrtc vue-advanced-cropper
npm install chart.js vue-chartjs
npm install dayjs marked emoji-mart-vue
```

### 4. Configure Environment

```bash
# Copy and edit .env
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure all API keys (Agora, payment gateways, etc.)
# See IMPLEMENTATION_GUIDE.md for details
```

### 5. Setup Database

```bash
php artisan migrate
php artisan db:seed
```

### 6. Build Assets

```bash
npm run build
```

---

## 📋 Post-Installation Checklist

- [ ] All composer packages installed
- [ ] All npm packages installed
- [ ] FFmpeg installed and configured
- [ ] Redis running
- [ ] MySQL database created and migrated
- [ ] Storage link created (`php artisan storage:link`)
- [ ] File permissions set (storage and bootstrap/cache)
- [ ] Environment variables configured
- [ ] Agora.io credentials added
- [ ] Payment gateway credentials configured
- [ ] Laravel Reverb configured and running
- [ ] Queue workers running
- [ ] Frontend assets built

---

## 🧪 Verify Installation

```bash
# Check PHP version and extensions
php -v
php -m | grep -E "curl|gd|mbstring|mysql|redis|zip"

# Check Composer packages
composer show | grep -E "agora|getid3|socialite"

# Check NPM packages
npm list --depth=0 | grep -E "agora|vue|laravel-echo"

# Check FFmpeg
ffmpeg -version
ffprobe -version

# Test database connection
php artisan db:show

# Test Redis connection
redis-cli ping

# Run health check
php artisan health:check
```

---

## 🔄 Update Packages

```bash
# Update Composer packages
composer update

# Update NPM packages
npm update

# Check for outdated packages
composer outdated
npm outdated
```

---

## ⚠️ Known Issues

### Agora SDK Installation

If you encounter issues installing Agora packages:

```bash
# Try specifying version
composer require agora/rtc-token-builder:^2.0

# Or clear cache first
composer clear-cache
composer require agora/rtc-token-builder
```

### FFmpeg Not Found

If FFmpeg is not in the default path:

```bash
# Find FFmpeg location
which ffmpeg

# Update .env with correct path
FFMPEG_BINARIES=/usr/local/bin/ffmpeg
FFPROBE_BINARIES=/usr/local/bin/ffprobe
```

### Redis Connection Issues

```bash
# Check Redis is running
sudo systemctl status redis

# Start Redis
sudo systemctl start redis

# Test connection
redis-cli ping
```

---

## 📚 Documentation Links

- [Agora RTC SDK](https://docs.agora.io/en/video-calling/overview/product-overview)
- [Laravel Socialite](https://laravel.com/docs/11.x/socialite)
- [Intervention Image](https://image.intervention.io/)
- [Laravel Excel](https://laravel-excel.com/)
- [Firebase PHP](https://firebase-php.readthedocs.io/)
- [Video.js](https://videojs.com/)
- [Chart.js](https://www.chartjs.org/)

---

Last Updated: 2026-01-10
Project: Multilingual Chat & Social Network Platform

# 🐳 Docker Deployment Guide

Complete guide for deploying the Multilingual Chat Platform using Docker.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Docker Services](#docker-services)
- [Makefile Commands](#makefile-commands)
- [Configuration](#configuration)
- [Development Environment](#development-environment)
- [Production Deployment](#production-deployment)
- [Troubleshooting](#troubleshooting)
- [Backup & Restore](#backup--restore)
- [Performance Tuning](#performance-tuning)

---

## 🌟 Overview

This Docker setup provides a complete, production-ready environment with:

- **9 Docker containers** orchestrated with Docker Compose
- **One-command installation** via Makefile
- **Separate profiles** for development and production
- **Automatic service management** with health checks
- **Persistent storage** for database and cache
- **Built-in tools** for development (phpMyAdmin, Redis Commander)

### Services Included

| Service | Description | Port |
|---------|-------------|------|
| **app** | Laravel PHP-FPM application | Internal |
| **nginx** | Web server + reverse proxy | 80, 443 |
| **mysql** | MySQL 8.0 database | 3306 |
| **redis** | Cache and queue storage | 6379 |
| **reverb** | WebSocket server | 8080 |
| **queue** | Background job processor | N/A |
| **scheduler** | Cron job runner | N/A |
| **node** | Vite dev server (dev only) | 5173 |
| **phpmyadmin** | Database UI (dev only) | 8081 |
| **redis-commander** | Redis UI (dev only) | 8082 |

---

## ✅ Prerequisites

### Required Software

```bash
# Docker
Docker Engine 20.10+
Docker Compose 2.0+

# For Makefile commands (optional)
GNU Make 4.0+
```

### Installation

#### Ubuntu/Debian

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt-get update
sudo apt-get install docker-compose-plugin

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker
```

#### macOS

```bash
# Install Docker Desktop
brew install --cask docker

# Or download from:
# https://www.docker.com/products/docker-desktop
```

#### Windows

Download and install [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop)

---

## 🚀 Quick Start

### 1. Clone Repository

```bash
git clone <repository-url>
cd chatbox
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit .env with your settings
nano .env
```

**Required settings:**
```env
DB_DATABASE=chatbox
DB_USERNAME=root
DB_PASSWORD=your_secure_password

AGORA_APP_ID=your_agora_app_id
AGORA_APP_CERTIFICATE=your_agora_certificate

# Payment gateways (optional)
VNPAY_TMN_CODE=
MOMO_PARTNER_CODE=
```

### 3. One-Command Installation

```bash
# Install everything automatically
make install
```

This will:
- ✅ Build Docker images
- ✅ Start all containers
- ✅ Install Composer dependencies
- ✅ Generate application key
- ✅ Run migrations and seeders
- ✅ Create storage link
- ✅ Install NPM dependencies
- ✅ Build frontend assets

### 4. Access Application

```
Frontend:  http://localhost
API:       http://localhost/api/v1/chat
WebSocket: ws://localhost:8080
```

---

## 🐳 Docker Services

### Application (app)

**Purpose**: Main Laravel application

**Dockerfile**: Multi-stage Alpine Linux image
- PHP 8.2-FPM
- All required extensions (GD, Redis, MySQL, etc.)
- FFmpeg for video processing
- Composer and npm pre-installed
- Optimized with OPcache

**Commands**:
```bash
# Access shell
make shell

# Run artisan commands
make artisan cmd="migrate"
make artisan cmd="queue:work"
```

### Web Server (nginx)

**Purpose**: HTTP server and reverse proxy

**Features**:
- Laravel routing
- WebSocket proxy for Reverb
- Gzip compression
- Static asset caching
- 100MB upload limit

**Configuration**: `docker/nginx/default.conf`

**Custom domain** (optional):
```nginx
server_name yourdomain.com;
```

### Database (mysql)

**Purpose**: MySQL 8.0 database

**Features**:
- UTF8MB4 charset
- InnoDB optimization
- Persistent volume
- Binary logging for replication

**Configuration**: `docker/mysql/my.cnf`

**Commands**:
```bash
# Access MySQL shell
make mysql

# Backup database
make backup-db

# Restore database
make restore-db file=backup.sql
```

### Cache & Queue (redis)

**Purpose**: Cache, session, and queue storage

**Features**:
- Persistent storage
- AOF persistence mode
- 7-Alpine image (lightweight)

**Commands**:
```bash
# Access Redis CLI
make redis

# In Redis CLI:
KEYS *
GET key_name
FLUSHALL  # Clear all data
```

### WebSocket Server (reverb)

**Purpose**: Real-time WebSocket server for Laravel Reverb

**Features**:
- Handles real-time events
- Message broadcasting
- Typing indicators
- Online status

**Commands**:
```bash
# Start Reverb
make reverb-start

# View logs
docker-compose logs -f reverb
```

### Queue Worker (queue)

**Purpose**: Process background jobs

**Features**:
- Auto-restart on failure
- 3 retry attempts
- 90-second timeout

**Jobs processed**:
- Email sending
- Payment processing
- Notification delivery
- File processing

**Commands**:
```bash
# View queue logs
docker-compose logs -f queue

# Restart queue worker
docker-compose restart queue
```

### Scheduler (scheduler)

**Purpose**: Laravel task scheduler (cron)

**Tasks**:
- Clean expired sessions
- Process scheduled notifications
- Generate daily reports
- Clean temporary files

Runs `php artisan schedule:run` every minute.

### Development Tools

#### Vite Dev Server (node)

**Purpose**: Hot module replacement for frontend

**Usage**:
```bash
make npm-dev
```

Access at: http://localhost:5173

#### phpMyAdmin

**Purpose**: Visual database management

**Usage**:
```bash
# Start dev environment
make dev
```

Access at: http://localhost:8081

**Login**:
- Server: mysql
- Username: root
- Password: (from .env DB_PASSWORD)

#### Redis Commander

**Purpose**: Visual Redis management

Access at: http://localhost:8082

---

## 🛠 Makefile Commands

### Installation & Setup

```bash
make install          # Full installation
make build            # Build Docker images
make up               # Start containers
make down             # Stop containers
make restart          # Restart containers
```

### Development

```bash
make dev              # Start dev environment (with phpMyAdmin, etc.)
make logs             # View container logs
make shell            # Access app container
make npm-dev          # Start Vite dev server
```

### Database

```bash
make migrate          # Run migrations
make migrate-fresh    # Fresh migrate
make seed             # Run seeders
make fresh            # Fresh migrate + seed
make mysql            # Access MySQL shell
make backup-db        # Backup database
make restore-db       # Restore database
```

### Artisan Commands

```bash
make artisan cmd="migrate"
make artisan cmd="queue:work"
make artisan cmd="cache:clear"
make artisan cmd="route:list"
```

### Cache Management

```bash
make cache-clear      # Clear all caches
make cache            # Cache config, routes, views
make optimize         # Optimize application
```

### Maintenance

```bash
make status           # Container status
make stats            # Resource usage
make permissions      # Fix file permissions
make clean            # Remove all containers & volumes
```

---

## ⚙️ Configuration

### Environment Variables

**Database**:
```env
DB_CONNECTION=mysql
DB_HOST=mysql          # Container name
DB_PORT=3306
DB_DATABASE=chatbox
DB_USERNAME=root
DB_PASSWORD=secure_password
```

**Redis**:
```env
REDIS_HOST=redis       # Container name
REDIS_PORT=6379
```

**Reverb**:
```env
REVERB_HOST=reverb     # Container name
REVERB_PORT=8080
```

### Port Mapping

Customize ports in `.env`:

```env
APP_PORT=80              # HTTP port
APP_SSL_PORT=443         # HTTPS port
DB_PORT=3306             # MySQL port
REDIS_PORT=6379          # Redis port
REVERB_PORT=8080         # WebSocket port
VITE_PORT=5173           # Vite dev server
PHPMYADMIN_PORT=8081     # phpMyAdmin
REDIS_COMMANDER_PORT=8082 # Redis Commander
```

### PHP Configuration

Edit `docker/php/local.ini`:

```ini
upload_max_filesize=100M    # Max upload size
post_max_size=100M          # Max POST size
memory_limit=512M           # Memory limit
max_execution_time=300      # Execution timeout
```

### MySQL Configuration

Edit `docker/mysql/my.cnf`:

```ini
max_connections=500         # Max connections
innodb_buffer_pool_size=1G  # InnoDB cache
max_allowed_packet=256M     # Max packet size
```

### Nginx Configuration

Edit `docker/nginx/default.conf`:

```nginx
client_max_body_size 100M;  # Upload limit

# Add SSL
listen 443 ssl;
ssl_certificate /etc/nginx/ssl/cert.pem;
ssl_certificate_key /etc/nginx/ssl/key.pem;
```

---

## 💻 Development Environment

### Start Development Mode

```bash
make dev
```

This starts all services including development tools:
- Application: http://localhost
- Vite: http://localhost:5173
- phpMyAdmin: http://localhost:8081
- Redis Commander: http://localhost:8082

### Hot Module Replacement

```bash
# Terminal 1: Start Vite
make npm-dev

# Terminal 2: Watch logs
make logs
```

Frontend changes will auto-reload!

### Database Management

**phpMyAdmin**: http://localhost:8081
- Visual query builder
- Import/export SQL
- Table structure editor

### Redis Management

**Redis Commander**: http://localhost:8082
- View all keys
- Edit values
- Monitor memory usage

### Debugging

```bash
# View specific service logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f reverb

# View all logs
make logs

# Access container shell
make shell

# Check container resources
make stats
```

---

## 🚀 Production Deployment

### 1. Pre-Deployment Checklist

- [ ] Update `.env` with production values
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure SSL certificates
- [ ] Set strong database passwords
- [ ] Configure backup strategy
- [ ] Set up monitoring

### 2. Build for Production

```bash
# Build optimized images
make build

# Start production services
make prod
```

### 3. Optimize Application

```bash
# Cache everything
make cache

# Optimize autoloader
docker-compose exec app composer install --optimize-autoloader --no-dev

# Build production assets
make npm-build
```

### 4. SSL/HTTPS Setup

**Option A: Let's Encrypt (Certbot)**

```bash
# Install Certbot
sudo apt-get install certbot

# Get certificate
sudo certbot certonly --standalone -d yourdomain.com

# Copy certificates
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem

# Update nginx config
nano docker/nginx/default.conf
```

**Option B: Self-signed (Development)**

```bash
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem
```

### 5. Process Management

Use Supervisor or systemd to ensure containers restart on boot:

```bash
# Create systemd service
sudo nano /etc/systemd/system/chatbox.service
```

```ini
[Unit]
Description=Chatbox Docker Compose
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/path/to/chatbox
ExecStart=/usr/bin/docker-compose up -d
ExecStop=/usr/bin/docker-compose down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
```

```bash
# Enable service
sudo systemctl enable chatbox
sudo systemctl start chatbox
```

### 6. Monitoring

```bash
# Check container health
make status

# Monitor resources
make stats

# View logs
make logs

# Set up log rotation
docker-compose logs --tail=1000 > /var/log/chatbox.log
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Port Already in Use

**Problem**: Port 80, 3306, or 6379 already in use

**Solution**:
```bash
# Find process using port
sudo lsof -i :80
sudo lsof -i :3306

# Kill process or change port in .env
APP_PORT=8000
DB_PORT=3307
```

#### 2. Permission Denied

**Problem**: Storage or cache permission issues

**Solution**:
```bash
make permissions
```

#### 3. Container Won't Start

**Problem**: Container crashes on startup

**Solution**:
```bash
# Check logs
docker-compose logs app

# Rebuild image
docker-compose build --no-cache app
docker-compose up -d
```

#### 4. Database Connection Failed

**Problem**: Can't connect to MySQL

**Solution**:
```bash
# Check MySQL is running
docker-compose ps mysql

# Check environment variables
docker-compose exec app env | grep DB_

# Verify connection
docker-compose exec app php artisan db:show
```

#### 5. WebSocket Not Working

**Problem**: Real-time features not working

**Solution**:
```bash
# Check Reverb is running
docker-compose ps reverb

# View Reverb logs
docker-compose logs -f reverb

# Restart Reverb
docker-compose restart reverb
```

#### 6. Out of Memory

**Problem**: PHP or MySQL runs out of memory

**Solution**:
```bash
# Increase PHP memory limit
nano docker/php/local.ini
# Set: memory_limit=1024M

# Increase MySQL buffer
nano docker/mysql/my.cnf
# Set: innodb_buffer_pool_size=2G

# Rebuild
docker-compose down
docker-compose up -d --build
```

### Useful Debugging Commands

```bash
# Check container resource usage
docker stats

# Inspect container
docker inspect chatbox_app

# View container processes
docker-compose exec app ps aux

# Check disk usage
docker system df

# Clean unused resources
docker system prune -a
```

---

## 💾 Backup & Restore

### Database Backup

#### Manual Backup

```bash
# Using Makefile
make backup-db

# Or directly
docker-compose exec mysql mysqldump -uroot -p$DB_PASSWORD chatbox > backup.sql
```

#### Automated Backup (Cron)

```bash
# Add to crontab
crontab -e
```

```bash
# Daily backup at 2 AM
0 2 * * * cd /path/to/chatbox && make backup-db
```

#### Backup to S3 (Optional)

```bash
# Install AWS CLI
pip install awscli

# Backup and upload
make backup-db
aws s3 cp backups/db_backup_*.sql s3://your-bucket/backups/
```

### Database Restore

```bash
# Using Makefile
make restore-db file=backup.sql

# Or directly
docker-compose exec -T mysql mysql -uroot -p$DB_PASSWORD chatbox < backup.sql
```

### Volume Backup

```bash
# Backup MySQL volume
docker run --rm \
  -v chatbox_mysql_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/mysql_volume.tar.gz /data

# Backup Redis volume
docker run --rm \
  -v chatbox_redis_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/redis_volume.tar.gz /data
```

### Full Backup

```bash
# Create backup directory
mkdir -p backups/$(date +%Y%m%d)

# Backup database
make backup-db

# Backup volumes
docker run --rm -v chatbox_mysql_data:/data -v $(pwd)/backups:/backup alpine tar czf /backup/mysql.tar.gz /data

# Backup .env and uploads
cp .env backups/$(date +%Y%m%d)/.env
cp -r storage/app/public backups/$(date +%Y%m%d)/uploads
```

---

## ⚡ Performance Tuning

### PHP Optimization

**docker/php/local.ini**:
```ini
# OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0

# Realpath cache
realpath_cache_size=4096K
realpath_cache_ttl=600
```

### MySQL Optimization

**docker/mysql/my.cnf**:
```ini
# InnoDB
innodb_buffer_pool_size=2G      # 70-80% of RAM
innodb_log_file_size=512M
innodb_flush_log_at_trx_commit=2
innodb_flush_method=O_DIRECT
innodb_buffer_pool_instances=8

# Query cache (if < MySQL 8.0)
query_cache_type=1
query_cache_size=64M
```

### Nginx Caching

**docker/nginx/default.conf**:
```nginx
# FastCGI cache
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=CACHEZONE:10m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

location ~ \.php$ {
    fastcgi_cache CACHEZONE;
    fastcgi_cache_valid 200 60m;
    fastcgi_cache_use_stale error timeout invalid_header http_500;
}
```

### Redis Optimization

```bash
# In production, use Redis for sessions and cache
# .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [Laravel Deployment](https://laravel.com/docs/11.x/deployment)
- [Nginx Configuration](https://nginx.org/en/docs/)

---

## 🆘 Support

### Getting Help

1. Check this guide for common issues
2. View container logs: `make logs`
3. Check Docker status: `make status`
4. Review [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)

### Reporting Issues

Include in your report:
```bash
# System info
docker --version
docker-compose --version

# Container status
make status

# Recent logs
make logs | tail -100
```

---

**Last Updated**: 2026-01-10
**Version**: 1.0.0

# Laravel 11 E-Commerce Platform

Full-featured Laravel 11 e-commerce platform for selling services, digital files, and online courses with multi-payment gateway support.

## Features

### 🛒 Core Functionality
- **Services**: SMM services (Instagram, YouTube, TikTok, etc.) with DHRU/GSM API integration
- **Products**: Physical products with stock management
- **Digital Files**: Downloadable files with download limits and encryption
- **Online Courses**: Video courses with lessons, progress tracking, and certificates
- **Tickets**: Support ticket system with auto-processing via API
- **Multi-Payment**: VNPay, Momo, ZaloPay, Stripe, PayPal, USDT, Fake (test)

### 🔐 Security & Access Control
- Role-Based Access Control (RBAC) with Spatie Permission
- Admin, Staff, and Customer roles
- Activity logging for all admin actions
- Secure file downloads with signed URLs

### 🌍 Multi-Language
- Vietnamese (VI)
- English (EN)
- Chinese Simplified (ZH)
- Easy language switcher

### 💳 Payment Gateways
- **VNPay** - Vietnamese payment gateway
- **Momo** - E-wallet payment
- **ZaloPay** - E-wallet payment
- **Stripe** - International credit cards
- **PayPal** - International payments
- **USDT** - Cryptocurrency (TRC20/ERC20)
- **Fake** - Test/sandbox mode

### 📊 Admin Dashboard
- Revenue and profit tracking
- Order management
- Stock management with low-stock alerts
- Ticket management
- User management
- Comprehensive reports (Sales, Products, Tickets)
- Export to PDF/Excel

### 🤖 AI Chatbot
- Auto-response for common queries
- Integration with OpenAI GPT
- Conversation logging
- Multi-language support

### 📱 Responsive Design
- Mobile-first responsive design
- Figma-style modern UI
- Drag-and-drop admin interface
- Interactive charts and graphs

## Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL/PostgreSQL/SQLite
- Redis (optional, for queues and caching)

## Installation

### Quick Deploy (Recommended)

```bash
# Clone repository
git clone <repository-url>
cd chatbox

# Run deployment script
chmod +x deploy.sh
./deploy.sh
```

### Manual Installation

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Build assets
npm run build

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env file
# Then run migrations and seeders
php artisan migrate --seed

# Link storage
php artisan storage:link

# Start server
php artisan serve
```

### Using Artisan Deploy Command

```bash
# Full deployment
php artisan deploy:full

# Skip seeding
php artisan deploy:full --no-seed

# Skip tests
php artisan deploy:full --no-test
```

## Default Credentials

After seeding, you can login with:

**Superadmin:**
- Email: superadmin@example.com
- Password: superadmin123

**Admin:**
- Email: admin@example.com
- Password: admin123

**Customer:**
- Email: customer1@example.com
- Password: customer123

## Configuration

### Payment Gateways

Configure payment gateways in `.env`:

```env
# VNPay
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret

# Momo
MOMO_PARTNER_CODE=your_partner_code
MOMO_ACCESS_KEY=your_access_key
MOMO_SECRET_KEY=your_secret_key

# Stripe
STRIPE_KEY=your_publishable_key
STRIPE_SECRET=your_secret_key

# PayPal
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=your_client_id
PAYPAL_SANDBOX_SECRET=your_secret
```

### API Integration

Configure DHRU/GSM APIs in `.env`:

```env
DHRU_API_URL=https://dhru.example.com/api
DHRU_API_KEY=your_api_key
DHRU_USERNAME=your_username

GSM_API_URL=https://gsm.example.com/api
GSM_API_KEY=your_api_key
```

### AI Chatbot

Configure OpenAI in `.env`:

```env
CHATBOT_ENABLED=true
OPENAI_API_KEY=your_openai_key
CHATBOT_MODEL=gpt-3.5-turbo
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter ServiceTest

# Run with coverage
php artisan test --coverage
```

## Validation

Validate system before deployment:

```bash
# Quick validation
php artisan validate:system

# Detailed validation
php artisan validate:system --detailed

# Export validation report
php artisan validate:system --export=report.json
```

## Queue Workers

For background jobs (payments, emails, notifications):

```bash
# Start queue worker
php artisan queue:work

# Or use Horizon (recommended)
php artisan horizon
```

## Scheduled Tasks

Add to crontab for scheduled tasks:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Project Structure

```
app/
├── Console/Commands/       # Artisan commands
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         # Admin controllers
│   │   ├── Frontend/      # Frontend controllers
│   │   └── Auth/          # Authentication controllers
│   └── Middleware/        # Custom middleware
├── Jobs/                  # Queue jobs
├── Models/                # Eloquent models
└── Services/              # Business logic services
    └── PaymentGateways/   # Payment gateway integrations

database/
├── factories/             # Model factories
├── migrations/            # Database migrations
└── seeders/               # Database seeders

resources/
├── css/                   # Stylesheets
├── js/                    # JavaScript
├── lang/                  # Multi-language files
│   ├── en/               # English
│   ├── vi/               # Vietnamese
│   └── zh/               # Chinese
└── views/                # Blade templates
    ├── admin/            # Admin views
    ├── frontend/         # Frontend views
    └── auth/             # Authentication views

routes/
├── web.php               # Web routes
├── api.php               # API routes
└── console.php           # Console commands

tests/
├── Feature/              # Feature tests
└── Unit/                 # Unit tests
```

## Features Overview

### For Customers
- Browse services, products, files, and courses
- Add to cart and checkout
- Multiple payment methods
- Download purchased files
- Access enrolled courses
- Track orders and tickets
- View purchase history
- AI chatbot support

### For Admins
- Comprehensive dashboard with statistics
- Manage products, services, files, courses
- Order management and fulfillment
- Ticket management with API integration
- User management with role assignment
- Generate reports (sales, revenue, profit)
- System settings configuration
- Activity logs and audit trail

## API Documentation

API endpoints are available at `/api/*`. Authentication uses Laravel Sanctum.

## Support

For issues and questions:
- Create an issue in the repository
- Check documentation in `/docs`
- Contact support team

## License

This project is proprietary software. All rights reserved.

## Credits

Built with:
- Laravel 11
- Spatie Packages (Permission, Activity Log)
- Chart.js
- Alpine.js
- SortableJS
- SweetAlert2

---

**Version:** 1.0.0
**Last Updated:** 2024-12-16

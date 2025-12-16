# 🚀 Web Installer Guide

## Laravel E-Commerce Platform - Easy Web Installation

This Laravel E-Commerce platform comes with a beautiful web-based installer that makes setup as easy as WordPress!

---

## 📋 Features

- ✅ **User-Friendly Interface** - Beautiful, intuitive design
- ✅ **Step-by-Step Process** - 5 easy steps to complete installation
- ✅ **Requirements Checker** - Automatically validates server requirements
- ✅ **Database Tester** - Tests database connection before proceeding
- ✅ **Auto Configuration** - Generates `.env` file automatically
- ✅ **One-Click Install** - Runs migrations and creates admin account
- ✅ **Security Lock** - Prevents re-installation after completion

---

## 🎯 Installation Steps

### Step 1: Upload Files

1. Upload all files to your web server
2. Point your domain to the `/public` directory
3. Ensure proper file permissions (755 for directories, 644 for files)

### Step 2: Access Installer

Navigate to your domain in a web browser:
```
http://yourdomain.com
```

If the application is not yet installed, you'll be automatically redirected to:
```
http://yourdomain.com/install
```

### Step 3: Welcome Screen

- Read the welcome message and feature list
- Review the checklist of things you'll need
- Click "Get Started →" to begin installation

### Step 4: Requirements Check

The installer will automatically check:

**PHP Version:**
- ✓ PHP 8.2.0 or higher

**PHP Extensions:**
- ✓ OpenSSL
- ✓ PDO
- ✓ Mbstring
- ✓ Tokenizer
- ✓ JSON
- ✓ cURL
- ✓ Fileinfo
- ✓ GD
- ✓ ZIP

**Directory Permissions:**
- ✓ storage/app (writable)
- ✓ storage/framework (writable)
- ✓ storage/logs (writable)
- ✓ bootstrap/cache (writable)
- ✓ .env (writable)

**If all requirements pass**, click "Continue →"

**If some requirements fail**, fix them before continuing:
- Contact your hosting provider for PHP extension issues
- Use FTP or SSH to fix file permissions:
  ```bash
  chmod -R 775 storage
  chmod -R 775 bootstrap/cache
  ```

### Step 5: Environment Configuration

Configure your application settings:

**Application Settings:**
- **App Name**: Your site name (e.g., "My Store")
- **App URL**: Your full domain URL (e.g., https://yourdomain.com)

**Database Settings:**
- **Database Type**: Choose MySQL, SQLite, or PostgreSQL
- **Database Host**: Usually `127.0.0.1` or `localhost`
- **Database Port**: `3306` for MySQL, `5432` for PostgreSQL
- **Database Name**: Your database name
- **Username**: Database username
- **Password**: Database password (leave blank if none)

**For SQLite:**
- Just provide the database file path
- Default: `/path/to/database/database.sqlite`

Click "Test & Continue →" to validate database connection.

### Step 6: Create Admin Account

Create your administrator account:

- **Full Name**: Your name
- **Email**: Your email (used for login)
- **Password**: Minimum 8 characters
- **Confirm Password**: Re-enter password

Click "🚀 Install Now" to complete installation.

**What happens next:**
- Application key is generated
- Database tables are created
- Roles and permissions are configured
- Your admin account is created
- Storage directories are linked

**This process takes 1-2 minutes. Do not close the window.**

### Step 7: Installation Complete!

🎉 **Success!** Your e-commerce platform is ready!

**What to do next:**

1. **Save your credentials** - You'll need them to login
2. **Delete install routes** - For security (optional)
3. **Access admin panel** - Click "Go to Admin"
4. **Configure payments** - Add payment gateway credentials
5. **Setup email** - Configure SMTP settings
6. **Add content** - Start adding products, services, files, courses

---

## 🔒 Security After Installation

### 1. Delete Install Routes (Optional)

For maximum security, remove installer access:

**Option A: Comment out installer routes**

Edit `bootstrap/app.php` and comment out:
```php
// then: function () {
//     Route::middleware('web')
//         ->group(base_path('routes/installer.php'));
// }
```

**Option B: Delete installer files**

```bash
rm -rf app/Http/Controllers/Installer
rm -rf resources/views/installer
rm routes/installer.php
rm app/Http/Middleware/CheckInstalled.php
```

### 2. Change Permissions

After installation, restrict permissions:
```bash
chmod 755 storage
chmod 755 bootstrap/cache
chmod 644 .env
```

---

## 🔧 Troubleshooting

### Issue: White Screen / Error 500

**Solution:**
1. Check `.env` file exists and is valid
2. Run: `php artisan config:clear`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Ensure all directories are writable

### Issue: Database Connection Failed

**Solutions:**
- Verify database credentials are correct
- Ensure database exists (create it first)
- Check database server is running
- For MySQL, try host `127.0.0.1` instead of `localhost`
- Check database port is correct

### Issue: Requirements Not Met

**Solutions:**

**PHP Version Too Low:**
- Upgrade PHP to 8.2+ through your hosting panel
- Or contact hosting provider

**Missing Extensions:**
- Install via package manager (Ubuntu/Debian):
  ```bash
  sudo apt-get install php8.2-mbstring php8.2-xml php8.2-curl php8.2-gd php8.2-zip
  ```
- Or contact hosting provider

**Permission Issues:**
- Fix via FTP/SSH:
  ```bash
  chmod -R 775 storage
  chmod -R 775 bootstrap/cache
  ```

### Issue: Already Installed Error

If you see "Application is already installed":

**To Re-Install:**
1. Delete `storage/installed` file
2. Drop all database tables
3. Delete `.env` file
4. Refresh browser and start again

---

## 🎨 Customization

### Change Installer Design

Edit these files:
- `resources/views/installer/layout.blade.php` - Main layout and CSS
- `resources/views/installer/*.blade.php` - Individual step pages

### Add Custom Requirements

Edit `app/Http/Controllers/Installer/InstallController.php`:
```php
public function requirements()
{
    // Add your custom requirements
    $requirements['custom'] = [
        'Your Requirement' => your_check_function(),
    ];
}
```

### Customize Installation Process

Edit `install()` method in InstallController to add:
- Custom seeders
- Additional configurations
- Third-party service setup

---

## 📱 Mobile Responsive

The installer is fully responsive and works on:
- ✅ Desktop (1920px+)
- ✅ Laptop (1366px+)
- ✅ Tablet (768px+)
- ✅ Mobile (320px+)

---

## 🌐 Multi-Language Support

The installer currently supports:
- English (default)

To add more languages, edit the view files and replace text strings.

---

## 💡 Best Practices

1. **Always use HTTPS** in production
2. **Use strong passwords** for admin account
3. **Backup database** before re-installing
4. **Test on staging** environment first
5. **Keep .env secure** - never commit to version control
6. **Use environment variables** for sensitive data
7. **Enable error logging** in production (.env: APP_DEBUG=false)

---

## 🆘 Support

Need help?

- **Documentation**: Check `/README.md`
- **Laravel Docs**: https://laravel.com/docs
- **GitHub Issues**: Create an issue on the repository
- **Email Support**: support@example.com

---

## ✅ Checklist

Before going live, ensure:

- [ ] Web installer completed successfully
- [ ] Admin account created and tested
- [ ] All payment gateways configured
- [ ] Email settings configured and tested
- [ ] Products/services added
- [ ] Test orders placed successfully
- [ ] Installer routes secured/deleted
- [ ] SSL certificate installed
- [ ] Backup system configured
- [ ] Error logging enabled
- [ ] Performance optimized (caching, CDN)
- [ ] Security headers configured

---

**Enjoy your new Laravel E-Commerce Platform! 🚀**

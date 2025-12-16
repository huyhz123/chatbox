<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InstallController extends Controller
{
    /**
     * Display installer welcome page
     */
    public function welcome()
    {
        return view('installer.welcome');
    }

    /**
     * Check system requirements
     */
    public function requirements()
    {
        $requirements = [
            'php' => [
                'name' => 'PHP Version',
                'required' => '8.2.0',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.2.0', '>=')
            ],
            'extensions' => [
                'OpenSSL' => extension_loaded('openssl'),
                'PDO' => extension_loaded('pdo'),
                'Mbstring' => extension_loaded('mbstring'),
                'Tokenizer' => extension_loaded('tokenizer'),
                'JSON' => extension_loaded('json'),
                'cURL' => extension_loaded('curl'),
                'Fileinfo' => extension_loaded('fileinfo'),
                'GD' => extension_loaded('gd'),
                'ZIP' => extension_loaded('zip'),
            ],
            'permissions' => [
                'storage/app' => is_writable(storage_path('app')),
                'storage/framework' => is_writable(storage_path('framework')),
                'storage/logs' => is_writable(storage_path('logs')),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
                '.env' => is_writable(base_path('.env')) || !file_exists(base_path('.env')),
            ]
        ];

        $allPassed = $requirements['php']['status'];
        foreach ($requirements['extensions'] as $status) {
            $allPassed = $allPassed && $status;
        }
        foreach ($requirements['permissions'] as $status) {
            $allPassed = $allPassed && $status;
        }

        return view('installer.requirements', compact('requirements', 'allPassed'));
    }

    /**
     * Display environment configuration form
     */
    public function environment()
    {
        return view('installer.environment');
    }

    /**
     * Save environment configuration
     */
    public function environmentSave(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'db_connection' => 'required|in:mysql,sqlite,pgsql',
            'db_host' => 'required_if:db_connection,mysql,pgsql',
            'db_port' => 'required_if:db_connection,mysql,pgsql',
            'db_database' => 'required',
            'db_username' => 'required_if:db_connection,mysql,pgsql',
            'db_password' => 'nullable',
        ]);

        // Test database connection
        try {
            $this->testDatabaseConnection($validated);
        } catch (\Exception $e) {
            return back()->withErrors(['database' => 'Database connection failed: ' . $e->getMessage()]);
        }

        // Save to .env
        $this->writeEnvironmentFile($validated);

        return redirect()->route('installer.admin');
    }

    /**
     * Display admin account creation form
     */
    public function admin()
    {
        return view('installer.admin');
    }

    /**
     * Create admin account and run installation
     */
    public function install(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            // Store admin info in session for later
            session(['admin_info' => $validated]);

            // Generate app key
            Artisan::call('key:generate', ['--force' => true]);

            // Clear config cache
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Run seeders (roles and permissions)
            Artisan::call('db:seed', [
                '--class' => 'RolePermissionSeeder',
                '--force' => true
            ]);

            // Create storage link
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            // Create admin user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // Assign superadmin role
            $user->assignRole('superadmin');

            // Create install lock file
            file_put_contents(storage_path('installed'), date('Y-m-d H:i:s'));

            return redirect()->route('installer.complete');

        } catch (\Exception $e) {
            return back()->withErrors(['installation' => 'Installation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Display installation complete page
     */
    public function complete()
    {
        $adminInfo = session('admin_info');
        session()->forget('admin_info');

        return view('installer.complete', compact('adminInfo'));
    }

    /**
     * Test database connection
     */
    protected function testDatabaseConnection($config)
    {
        if ($config['db_connection'] === 'sqlite') {
            $database = $config['db_database'];
            if (!file_exists($database)) {
                touch($database);
            }
        }

        config([
            'database.default' => $config['db_connection'],
            'database.connections.' . $config['db_connection'] => [
                'driver' => $config['db_connection'],
                'host' => $config['db_host'] ?? null,
                'port' => $config['db_port'] ?? null,
                'database' => $config['db_database'],
                'username' => $config['db_username'] ?? null,
                'password' => $config['db_password'] ?? null,
            ]
        ]);

        DB::purge($config['db_connection']);
        DB::reconnect($config['db_connection']);
        DB::connection()->getPdo();
    }

    /**
     * Write environment file
     */
    protected function writeEnvironmentFile($config)
    {
        $envContent = "APP_NAME=\"{$config['app_name']}\"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL={$config['app_url']}
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION={$config['db_connection']}
DB_HOST={$config['db_host']}
DB_PORT={$config['db_port']}
DB_DATABASE={$config['db_database']}
DB_USERNAME={$config['db_username']}
DB_PASSWORD={$config['db_password']}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_FROM_ADDRESS=\"hello@example.com\"
MAIL_FROM_NAME=\"\${APP_NAME}\"

# Payment Gateways - Configure later
VNPAY_TMN_CODE=
VNPAY_HASH_SECRET=
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html

MOMO_PARTNER_CODE=
MOMO_ACCESS_KEY=
MOMO_SECRET_KEY=

STRIPE_KEY=
STRIPE_SECRET=

PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=
PAYPAL_SANDBOX_SECRET=

# AI Chatbot - Configure later
CHATBOT_ENABLED=false
OPENAI_API_KEY=
";

        file_put_contents(base_path('.env'), $envContent);
    }
}

<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        $serverInfo = [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        $allPassed = $requirements['php']['status'];
        foreach ($requirements['extensions'] as $status) {
            $allPassed = $allPassed && $status;
        }
        foreach ($requirements['permissions'] as $status) {
            $allPassed = $allPassed && $status;
        }

        return view('installer.requirements', compact('requirements', 'allPassed', 'serverInfo'));
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
            'import_demo_data' => 'boolean',
        ]);

        $installationSteps = [];
        $startTime = microtime(true);

        try {
            // Store admin info in session for later
            session(['admin_info' => $validated]);

            // Step 1: Generate app key
            Artisan::call('key:generate', ['--force' => true]);
            $installationSteps[] = 'Application key generated';

            // Step 2: Clear config cache
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            $installationSteps[] = 'Cache cleared';

            // Step 3: Run migrations
            Artisan::call('migrate', ['--force' => true]);
            $installationSteps[] = 'Database tables created';

            // Step 4: Run seeders (roles and permissions)
            Artisan::call('db:seed', [
                '--class' => 'RolePermissionSeeder',
                '--force' => true
            ]);
            $installationSteps[] = 'Roles and permissions configured';

            // Step 5: Create storage link
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
            $installationSteps[] = 'Storage directories linked';

            // Step 6: Create admin user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // Assign superadmin role
            $user->assignRole('superadmin');
            $installationSteps[] = 'Administrator account created';

            // Step 7: Import demo data if requested
            if ($request->boolean('import_demo_data')) {
                $this->importDemoData();
                $installationSteps[] = 'Demo data imported (categories, services, products, files, courses)';
                session(['demo_data_imported' => true]);
            }

            // Step 8: Optimize application
            $this->optimizeApplication();
            $installationSteps[] = 'Application optimized';

            // Generate installation report
            $endTime = microtime(true);
            $installationTime = round($endTime - $startTime, 2);

            $report = $this->generateInstallationReport($installationSteps, $installationTime);
            session(['installation_report' => $report]);

            // Create install lock file
            file_put_contents(storage_path('installed'), json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'demo_data' => $request->boolean('import_demo_data'),
            ]));

            return redirect()->route('installer.complete');

        } catch (\Exception $e) {
            // Rollback on failure
            $this->rollbackInstallation();

            return back()->withErrors([
                'installation' => 'Installation failed: ' . $e->getMessage() . ' Please check your configuration and try again.'
            ])->withInput();
        }
    }

    /**
     * Display installation complete page
     */
    public function complete()
    {
        $adminInfo = session('admin_info');
        $installationReport = session('installation_report');
        $demoDataImported = session('demo_data_imported', false);

        session()->forget(['admin_info', 'installation_report', 'demo_data_imported']);

        return view('installer.complete', compact('adminInfo', 'installationReport', 'demoDataImported'));
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

    /**
     * Import demo data for testing
     */
    protected function importDemoData()
    {
        $seeders = [
            'CategorySeeder',
            'ServiceSeeder',
            'ProductSeeder',
            'FileSeeder',
            'CourseSeeder',
        ];

        foreach ($seeders as $seeder) {
            try {
                Artisan::call('db:seed', [
                    '--class' => $seeder,
                    '--force' => true
                ]);
            } catch (\Exception $e) {
                // Continue even if some seeders fail
                \Log::warning("Seeder {$seeder} failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Optimize application after installation
     */
    protected function optimizeApplication()
    {
        try {
            // Cache routes for faster routing
            Artisan::call('route:cache');

            // Cache config for faster config loading
            Artisan::call('config:cache');

            // Cache views for faster view loading
            Artisan::call('view:cache');
        } catch (\Exception $e) {
            // Optimization is not critical, continue anyway
            \Log::warning("Optimization failed: " . $e->getMessage());
        }
    }

    /**
     * Generate detailed installation report
     */
    protected function generateInstallationReport($steps, $time)
    {
        return [
            'installation_date' => date('Y-m-d H:i:s'),
            'installation_time' => $time . ' seconds',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_type' => config('database.default'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'steps_completed' => $steps,
            'total_steps' => count($steps),
        ];
    }

    /**
     * Rollback installation on failure
     */
    protected function rollbackInstallation()
    {
        try {
            // Reset database migrations
            if (file_exists(base_path('.env'))) {
                Artisan::call('migrate:reset', ['--force' => true]);
            }

            // Remove .env file
            if (file_exists(base_path('.env'))) {
                @unlink(base_path('.env'));
            }

            // Remove install lock if exists
            if (file_exists(storage_path('installed'))) {
                @unlink(storage_path('installed'));
            }

            // Clear all caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

        } catch (\Exception $e) {
            // Log rollback failure but don't throw
            \Log::error("Rollback failed: " . $e->getMessage());
        }
    }
}

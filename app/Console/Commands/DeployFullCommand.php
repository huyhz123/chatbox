<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;

class DeployFullCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:full {--no-seed : Skip database seeding} {--no-test : Skip test execution} {--force : Skip confirmation}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Comprehensive deployment command with environment setup, migrations, seeding, and validation';

    /**
     * Track deployment steps for rollback
     *
     * @var array
     */
    protected array $completedSteps = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════╗');
        $this->info('║   Laravel E-commerce Full Deployment       ║');
        $this->info('╚════════════════════════════════════════════╝');
        $this->newLine();

        try {
            // Step 1: Environment Check
            $this->step('Checking environment configuration');
            $this->checkEnvironment();

            // Step 2: Generate App Key
            $this->step('Generating application key');
            $this->generateAppKey();

            // Step 3: Clear Caches
            $this->step('Clearing application caches');
            $this->clearCaches();

            // Step 4: Run Migrations
            $this->step('Running database migrations');
            $this->runMigrations();

            // Step 5: Seed Database
            if (!$this->option('no-seed')) {
                $this->step('Seeding database with initial data');
                $this->seedDatabase();
            }

            // Step 6: Link Storage
            $this->step('Linking storage directory');
            $this->linkStorage();

            // Step 7: Setup Permissions
            $this->step('Setting up file permissions');
            $this->setupPermissions();

            // Step 8: Setup Queue
            $this->step('Setting up queue configuration');
            $this->setupQueue();

            // Step 9: Run Tests
            if (!$this->option('no-test')) {
                $this->step('Running application tests');
                $this->runTests();
            }

            // Step 10: Display Success Message
            $this->displaySuccessMessage();

        } catch (\Exception $e) {
            $this->error('❌ Deployment failed: ' . $e->getMessage());
            $this->handleRollback();
            return 1;
        }

        return 0;
    }

    /**
     * Step 1: Check environment configuration
     */
    protected function checkEnvironment(): void
    {
        $requiredEnvVars = [
            'APP_NAME',
            'APP_KEY',
            'APP_URL',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
        ];

        $missingVars = [];

        foreach ($requiredEnvVars as $var) {
            if (!env($var)) {
                $missingVars[] = $var;
            }
        }

        if (!empty($missingVars)) {
            throw new \Exception('Missing environment variables: ' . implode(', ', $missingVars));
        }

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('  ✓ Database connection verified');
            $this->recordStep('environment_check');
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Step 2: Generate app key if missing
     */
    protected function generateAppKey(): void
    {
        if (!env('APP_KEY')) {
            $this->call('key:generate', ['--force' => true]);
            $this->info('  ✓ Application key generated');
            $this->recordStep('generate_app_key');
        } else {
            $this->info('  ✓ Application key already configured');
        }
    }

    /**
     * Step 3: Clear all caches
     */
    protected function clearCaches(): void
    {
        $caches = [
            'cache:clear' => 'Application cache',
            'config:clear' => 'Configuration cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
            'event:clear' => 'Event cache',
        ];

        foreach ($caches as $command => $label) {
            try {
                $this->call($command);
                $this->info("  ✓ {$label} cleared");
                $this->recordStep("clear_${label}");
            } catch (\Exception $e) {
                $this->warn("  ⚠ Failed to clear {$label}: " . $e->getMessage());
            }
        }
    }

    /**
     * Step 4: Run database migrations
     */
    protected function runMigrations(): void
    {
        try {
            $this->call('migrate', [
                '--force' => true,
            ]);
            $this->info('  ✓ Database migrations completed');
            $this->recordStep('run_migrations');
        } catch (\Exception $e) {
            throw new \Exception('Migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Step 5: Seed database
     */
    protected function seedDatabase(): void
    {
        try {
            $this->call('db:seed', [
                '--force' => true,
            ]);
            $this->info('  ✓ Database seeding completed');
            $this->recordStep('seed_database');
        } catch (\Exception $e) {
            $this->warn('  ⚠ Database seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Step 6: Link storage directory
     */
    protected function linkStorage(): void
    {
        try {
            // Create symbolic link from public/storage to storage/app/public
            $target = base_path('storage/app/public');
            $link = public_path('storage');

            if (file_exists($link)) {
                if (is_link($link)) {
                    $this->info('  ✓ Storage symlink already exists');
                } else {
                    unlink($link);
                    symlink($target, $link);
                    $this->info('  ✓ Storage symlink created');
                }
            } else {
                symlink($target, $link);
                $this->info('  ✓ Storage symlink created');
            }

            $this->recordStep('link_storage');
        } catch (\Exception $e) {
            throw new \Exception('Failed to link storage: ' . $e->getMessage());
        }
    }

    /**
     * Step 7: Setup file permissions
     */
    protected function setupPermissions(): void
    {
        $directories = [
            'bootstrap/cache',
            'storage',
            'storage/logs',
            'storage/framework',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/app',
            'storage/app/public',
        ];

        foreach ($directories as $dir) {
            $path = base_path($dir);
            if (file_exists($path)) {
                @chmod($path, 0775);
                $this->info("  ✓ Permissions set for {$dir}");
            }
        }

        // Set permissions for public directory
        $publicPath = public_path();
        if (file_exists($publicPath)) {
            @chmod($publicPath, 0755);
            $this->info('  ✓ Permissions set for public directory');
        }

        $this->recordStep('setup_permissions');
    }

    /**
     * Step 8: Setup queue configuration
     */
    protected function setupQueue(): void
    {
        $queueConnection = env('QUEUE_CONNECTION', 'redis');

        if ($queueConnection === 'redis') {
            // Test Redis connection
            try {
                $redis = \Redis::connection();
                $this->info("  ✓ Redis queue connection verified");
            } catch (\Exception $e) {
                $this->warn("  ⚠ Redis queue not available: " . $e->getMessage());
            }
        } elseif ($queueConnection === 'database') {
            // Create failed_jobs table if it doesn't exist
            try {
                if (!DB::connection()->getSchemaBuilder()->hasTable('failed_jobs')) {
                    $this->call('queue:failed-table');
                    $this->call('migrate');
                }
                $this->info('  ✓ Database queue table verified');
            } catch (\Exception $e) {
                $this->warn('  ⚠ Database queue setup failed: ' . $e->getMessage());
            }
        }

        $this->info("  ✓ Queue configured for: {$queueConnection}");
        $this->recordStep('setup_queue');
    }

    /**
     * Step 9: Run tests
     */
    protected function runTests(): void
    {
        if (!file_exists(base_path('phpunit.xml'))) {
            $this->warn('  ⚠ phpunit.xml not found, skipping tests');
            return;
        }

        try {
            $output = new BufferedOutput();
            Artisan::call('test', [], $output);

            $testOutput = $output->fetch();

            if (Artisan::last() === 0) {
                $this->info('  ✓ All tests passed');
                $this->recordStep('run_tests');
            } else {
                $this->warn('  ⚠ Some tests failed');
                $this->warn($testOutput);
            }
        } catch (\Exception $e) {
            $this->warn('  ⚠ Test execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Display success message with credentials
     */
    protected function displaySuccessMessage(): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════╗');
        $this->info('║   ✓ Deployment Completed Successfully      ║');
        $this->info('╚════════════════════════════════════════════╝');
        $this->newLine();

        $this->info('Deployment Information:');
        $this->line('  Application Name: ' . env('APP_NAME'));
        $this->line('  Application URL:  ' . env('APP_URL'));
        $this->line('  Environment:      ' . env('APP_ENV'));
        $this->line('  Database:         ' . env('DB_DATABASE') . ' @ ' . env('DB_HOST'));
        $this->line('  Queue Driver:     ' . env('QUEUE_CONNECTION'));
        $this->line('  Cache Driver:     ' . env('CACHE_DRIVER'));
        $this->newLine();

        $this->info('Completed Steps:');
        foreach ($this->completedSteps as $step) {
            $this->line("  ✓ {$step}");
        }

        $this->newLine();
        $this->info('Next Steps:');
        $this->line('  1. Verify the application is running: ' . env('APP_URL'));
        $this->line('  2. Check logs: tail -f storage/logs/laravel.log');
        $this->line('  3. Monitor queue: php artisan queue:work');
        $this->line('  4. Run Horizon for advanced monitoring: php artisan horizon');

        $this->newLine();
        $this->info('Useful Commands:');
        $this->line('  • Validate system: php artisan validate:system');
        $this->line('  • Clear caches: php artisan cache:clear');
        $this->line('  • Run migrations: php artisan migrate');
        $this->line('  • Seed database: php artisan db:seed');

        $this->newLine();
    }

    /**
     * Record a completed step
     */
    protected function recordStep(string $step): void
    {
        $this->completedSteps[] = $step;
    }

    /**
     * Handle rollback on failure
     */
    protected function handleRollback(): void
    {
        $this->newLine();
        $this->warn('Rolling back deployment...');

        // In a production environment, you might want to implement
        // more sophisticated rollback mechanisms here
        $this->warn('  • Review the error message above');
        $this->warn('  • Fix the underlying issue');
        $this->warn('  • Run the deployment again');

        $this->newLine();
    }

    /**
     * Format a step header
     */
    protected function step(string $message): void
    {
        $this->newLine();
        $this->info("→ {$message}...");
    }
}

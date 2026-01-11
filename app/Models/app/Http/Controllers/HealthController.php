<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * Basic health check
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'service' => 'Multilingual Chat Platform',
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    /**
     * Detailed health check with dependencies
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $overallStatus = collect($checks)->every(fn($check) => $check['status'] === 'ok')
            ? 'healthy'
            : 'degraded';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
            ],
        ], $overallStatus === 'healthy' ? 200 : 503);
    }

    /**
     * Application check
     */
    private function checkApp(): array
    {
        try {
            return [
                'status' => 'ok',
                'debug' => config('app.debug'),
                'env' => config('app.env'),
                'url' => config('app.url'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Database check
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            // Check if we can query
            $usersCount = DB::table('chat_users')->count();

            return [
                'status' => 'ok',
                'connection' => DB::connection()->getDatabaseName(),
                'latency_ms' => $latency,
                'users_count' => $usersCount,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Redis check
     */
    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $latency = round((microtime(true) - $start) * 1000, 2);

            // Test cache
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'ok', 10);
            $cacheWorks = Cache::get($testKey) === 'ok';
            Cache::forget($testKey);

            return [
                'status' => 'ok',
                'latency_ms' => $latency,
                'cache_works' => $cacheWorks,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Storage check
     */
    private function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $publicPath = storage_path('app/public');

            return [
                'status' => 'ok',
                'writable' => is_writable($storagePath),
                'public_link_exists' => is_link(public_path('storage')),
                'disk_space' => [
                    'free' => $this->formatBytes(disk_free_space($storagePath)),
                    'total' => $this->formatBytes(disk_total_space($storagePath)),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Queue check
     */
    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            $queueSize = 0;

            // Try to get queue size (depends on driver)
            if ($connection === 'redis') {
                $queueSize = Redis::llen('queues:default');
            }

            return [
                'status' => 'ok',
                'connection' => $connection,
                'default_queue' => 'default',
                'pending_jobs' => $queueSize,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Readiness probe (for Kubernetes)
     */
    public function ready(): JsonResponse
    {
        // Check if app can serve traffic
        try {
            DB::connection()->getPdo();
            Redis::ping();

            return response()->json(['status' => 'ready']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'not_ready',
                'reason' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Liveness probe (for Kubernetes)
     */
    public function live(): JsonResponse
    {
        // Check if app is alive (basic check)
        return response()->json(['status' => 'alive']);
    }

    /**
     * Metrics endpoint (Prometheus format)
     */
    public function metrics(): string
    {
        $metrics = [];

        // App metrics
        $metrics[] = '# HELP app_info Application information';
        $metrics[] = '# TYPE app_info gauge';
        $metrics[] = sprintf('app_info{version="%s",env="%s"} 1', config('app.version', '1.0.0'), config('app.env'));

        // Database metrics
        try {
            $usersCount = DB::table('chat_users')->count();
            $metrics[] = '# HELP users_total Total number of registered users';
            $metrics[] = '# TYPE users_total gauge';
            $metrics[] = "users_total $usersCount";

            $messagesCount = DB::table('messages')->count();
            $metrics[] = '# HELP messages_total Total number of messages';
            $metrics[] = '# TYPE messages_total gauge';
            $metrics[] = "messages_total $messagesCount";

            $activeStreams = DB::table('live_streams')->where('status', 'live')->count();
            $metrics[] = '# HELP active_streams Current number of live streams';
            $metrics[] = '# TYPE active_streams gauge';
            $metrics[] = "active_streams $activeStreams";
        } catch (\Exception $e) {
            // Database not available
        }

        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $metrics[] = '# HELP php_memory_usage PHP memory usage in bytes';
        $metrics[] = '# TYPE php_memory_usage gauge';
        $metrics[] = "php_memory_usage $memoryUsage";

        return response(implode("\n", $metrics))
            ->header('Content-Type', 'text/plain; version=0.0.4');
    }
}

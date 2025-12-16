<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    private int $maxAttempts = 2;
    private int $backoff = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $reportType,
        private ?string $period = null,
        private ?array $filters = null,
        private ?string $userId = null
    ) {
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Generating report started', [
                'report_type' => $this->reportType,
                'period' => $this->period ?? 'current',
                'attempt' => $this->attempts(),
            ]);

            // Validate report type
            if (!$this->isValidReportType($this->reportType)) {
                throw new Exception("Invalid report type: {$this->reportType}");
            }

            // Determine date range
            $dateRange = $this->getDateRange($this->period);

            // Generate report data
            $reportData = match ($this->reportType) {
                'sales' => $this->generateSalesReport($dateRange),
                'tickets' => $this->generateTicketsReport($dateRange),
                'users' => $this->generateUsersReport($dateRange),
                'payments' => $this->generatePaymentsReport($dateRange),
                'revenue' => $this->generateRevenueReport($dateRange),
                default => throw new Exception("Unsupported report type: {$this->reportType}"),
            };

            // Generate file
            $filePath = $this->saveReportFile($reportData);

            Log::info('Report generated successfully', [
                'report_type' => $this->reportType,
                'period' => $this->period ?? 'current',
                'file_path' => $filePath,
                'records' => count($reportData['data'] ?? []),
            ]);

            // Send notification if user specified
            if ($this->userId !== null) {
                $this->notifyUser($filePath);
            }
        } catch (Exception $exception) {
            Log::error('Error generating report', [
                'report_type' => $this->reportType,
                'period' => $this->period ?? 'current',
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->maxAttempts) {
                Log::critical('Report generation failed permanently', [
                    'report_type' => $this->reportType,
                    'period' => $this->period ?? 'current',
                ]);
                $this->fail($exception);
            } else {
                $this->release($this->backoff);
            }
        }
    }

    /**
     * Generate sales report.
     */
    private function generateSalesReport(array $dateRange): array
    {
        $query = \DB::table('orders')
            ->whereBetween('created_at', $dateRange)
            ->select(
                \DB::raw('DATE(created_at) as date'),
                \DB::raw('COUNT(*) as total_orders'),
                \DB::raw('SUM(total) as total_amount')
            )
            ->groupBy('date')
            ->orderBy('date');

        if ($this->filters) {
            $query = $this->applyFilters($query, $this->filters);
        }

        return [
            'type' => 'sales',
            'period' => $this->period ?? 'current',
            'generated_at' => now(),
            'data' => $query->get()->toArray(),
        ];
    }

    /**
     * Generate tickets report.
     */
    private function generateTicketsReport(array $dateRange): array
    {
        $query = \DB::table('tickets')
            ->whereBetween('created_at', $dateRange)
            ->select(
                'status',
                \DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->orderBy('count', 'desc');

        return [
            'type' => 'tickets',
            'period' => $this->period ?? 'current',
            'generated_at' => now(),
            'data' => $query->get()->toArray(),
        ];
    }

    /**
     * Generate users report.
     */
    private function generateUsersReport(array $dateRange): array
    {
        $query = \DB::table('users')
            ->whereBetween('created_at', $dateRange)
            ->select(
                \DB::raw('DATE(created_at) as date'),
                \DB::raw('COUNT(*) as new_users')
            )
            ->groupBy('date')
            ->orderBy('date');

        return [
            'type' => 'users',
            'period' => $this->period ?? 'current',
            'generated_at' => now(),
            'data' => $query->get()->toArray(),
        ];
    }

    /**
     * Generate payments report.
     */
    private function generatePaymentsReport(array $dateRange): array
    {
        $query = \DB::table('payments')
            ->whereBetween('created_at', $dateRange)
            ->select(
                'status',
                \DB::raw('COUNT(*) as count'),
                \DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('status')
            ->orderBy('count', 'desc');

        return [
            'type' => 'payments',
            'period' => $this->period ?? 'current',
            'generated_at' => now(),
            'data' => $query->get()->toArray(),
        ];
    }

    /**
     * Generate revenue report.
     */
    private function generateRevenueReport(array $dateRange): array
    {
        $query = \DB::table('payments')
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->select(
                \DB::raw('DATE(created_at) as date'),
                \DB::raw('SUM(amount) as revenue'),
                \DB::raw('COUNT(*) as transactions')
            )
            ->groupBy('date')
            ->orderBy('date');

        return [
            'type' => 'revenue',
            'period' => $this->period ?? 'current',
            'generated_at' => now(),
            'data' => $query->get()->toArray(),
        ];
    }

    /**
     * Get date range based on period.
     */
    private function getDateRange(?string $period): array
    {
        $end = Carbon::now();

        $start = match ($period ?? 'monthly') {
            'daily' => $end->clone()->subDay(),
            'weekly' => $end->clone()->subWeek(),
            'monthly' => $end->clone()->subMonth(),
            'quarterly' => $end->clone()->subQuarter(),
            'yearly' => $end->clone()->subYear(),
            default => $end->clone()->subMonth(),
        };

        return [$start, $end];
    }

    /**
     * Validate report type.
     */
    private function isValidReportType(string $type): bool
    {
        return in_array($type, ['sales', 'tickets', 'users', 'payments', 'revenue']);
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    /**
     * Save report to file.
     */
    private function saveReportFile(array $reportData): string
    {
        $fileName = sprintf(
            'reports/%s/%s_%s.json',
            $reportData['type'],
            $reportData['type'],
            now()->format('Y-m-d_H-i-s')
        );

        Storage::disk('local')->put($fileName, json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $fileName;
    }

    /**
     * Notify user about report completion.
     */
    private function notifyUser(string $filePath): void
    {
        Log::info('Report notification sent', [
            'user_id' => $this->userId,
            'report_type' => $this->reportType,
            'file_path' => $filePath,
        ]);

        // Dispatch SendEmailJob to notify user
        // dispatch(new SendEmailJob(
        //     $user->email,
        //     "Your {$this->reportType} report is ready",
        //     'emails.report-ready',
        //     ['file_path' => $filePath, 'report_type' => $this->reportType]
        // ));
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::critical('GenerateReportJob failed permanently', [
            'report_type' => $this->reportType,
            'period' => $this->period ?? 'current',
            'exception' => $exception->getMessage(),
        ]);
    }
}

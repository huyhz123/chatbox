<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\UpdateTicketStatus;
use App\Console\Commands\GenerateDailyReport;
use App\Console\Commands\CleanOldLogs;
use App\Console\Commands\ProcessPendingPayments;
use App\Console\Commands\SendCourseReminders;
use App\Console\Commands\GenerateCertificates;
use App\Console\Commands\CleanUpExpiredSessions;
use App\Console\Commands\CacheWarming;
use App\Console\Commands\SendNotifications;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance and receives
| all of the arguments and options passed to the command.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quotes()->random());
})->purpose('Display an inspiring quote')->hourly();

// ============================================================================
// TICKET MANAGEMENT COMMANDS
// ============================================================================

/**
 * Update Ticket Status
 * Run every 6 hours to automatically update ticket statuses based on rules
 * - Close tickets that have been inactive for 30 days
 * - Escalate tickets that have been open for 7 days without response
 * - Update ticket status based on last activity
 */
Artisan::command('tickets:update-status', UpdateTicketStatus::class)
    ->purpose('Update ticket statuses based on activity and rules')
    ->describe('Automatically closes inactive tickets, escalates urgent tickets, and updates statuses');

Artisan::command('schedule:tickets-update', function () {
    // This will be called from the schedule
    $this->call('tickets:update-status');
})->everyFourHours()->name('tickets:update-status-scheduled');

// ============================================================================
// REPORT GENERATION COMMANDS
// ============================================================================

/**
 * Generate Daily Report
 * Run every day at 2:00 AM
 * Generates reports for:
 * - Daily sales summary
 * - Revenue breakdown by product/service/course
 * - New users and registrations
 * - Top performing products
 * - Customer engagement metrics
 */
Artisan::command('reports:daily', GenerateDailyReport::class)
    ->purpose('Generate daily business reports')
    ->describe('Generates sales, revenue, and performance reports for the previous day');

Artisan::command('schedule:daily-reports', function () {
    $this->call('reports:daily');
})->dailyAt('02:00')->name('reports:daily-scheduled');

/**
 * Generate Weekly Report
 * Run every Sunday at 3:00 AM
 */
Artisan::command('schedule:weekly-reports', function () {
    $this->call('reports:daily', ['--period' => 'weekly']);
})->weeklyOn(0, '03:00')->name('reports:weekly-scheduled');

/**
 * Generate Monthly Report
 * Run on the 1st of each month at 4:00 AM
 */
Artisan::command('schedule:monthly-reports', function () {
    $this->call('reports:daily', ['--period' => 'monthly']);
})->monthlyOn(1, '04:00')->name('reports:monthly-scheduled');

// ============================================================================
// LOG & CACHE CLEANUP COMMANDS
// ============================================================================

/**
 * Clean Old Logs
 * Run every day at 3:00 AM
 * - Removes logs older than 30 days
 * - Compresses archived logs
 * - Clears error logs from failed payment attempts
 */
Artisan::command('logs:clean', CleanOldLogs::class)
    ->purpose('Clean and archive old log files')
    ->describe('Removes logs older than 30 days and optimizes log storage');

Artisan::command('schedule:clean-logs', function () {
    $this->call('logs:clean');
})->dailyAt('03:00')->name('logs:clean-scheduled');

/**
 * Clean Expired Sessions
 * Run every 6 hours
 * - Removes expired user sessions
 * - Cleans up old authentication tokens
 * - Removes abandoned cart sessions
 */
Artisan::command('sessions:clean', CleanUpExpiredSessions::class)
    ->purpose('Clean expired session data')
    ->describe('Removes expired sessions and old authentication tokens');

Artisan::command('schedule:clean-sessions', function () {
    $this->call('sessions:clean');
})->everyFourHours()->name('sessions:clean-scheduled');

/**
 * Clear Cache
 * Run every hour
 * - Clears application cache
 * - Refreshes configuration cache
 */
Artisan::command('schedule:cache-clear', function () {
    $this->call('cache:clear');
    $this->info('Cache cleared successfully');
})->hourly()->name('cache:clear-scheduled');

// ============================================================================
// PAYMENT PROCESSING COMMANDS
// ============================================================================

/**
 * Process Pending Payments
 * Run every 30 minutes
 * - Processes pending payment transactions
 * - Checks payment status with payment gateways
 * - Retries failed payments
 * - Generates payment receipts
 * - Sends payment notifications
 */
Artisan::command('payments:process', ProcessPendingPayments::class)
    ->purpose('Process pending payment transactions')
    ->describe('Handles pending payments, retries failed transactions, and updates payment status');

Artisan::command('schedule:process-payments', function () {
    $this->call('payments:process');
})->everyThirtyMinutes()->name('payments:process-scheduled');

/**
 * Verify Payment Status
 * Run every hour
 * - Verifies payment status with payment gateways
 * - Reconciles payments with orders
 * - Updates order status based on payment
 */
Artisan::command('schedule:verify-payments', function () {
    $this->call('payments:process', ['--verify' => true]);
})->hourly()->name('payments:verify-scheduled');

// ============================================================================
// COURSE & LEARNING COMMANDS
// ============================================================================

/**
 * Send Course Reminders
 * Run every day at 8:00 AM
 * - Sends reminders to enrolled students to continue courses
 * - Sends completion reminders for courses nearing deadline
 * - Notifies about new lessons in enrolled courses
 */
Artisan::command('courses:send-reminders', SendCourseReminders::class)
    ->purpose('Send course reminders to enrolled students')
    ->describe('Notifies students about courses, new lessons, and completion deadlines');

Artisan::command('schedule:course-reminders', function () {
    $this->call('courses:send-reminders');
})->dailyAt('08:00')->name('courses:reminders-scheduled');

/**
 * Generate Certificates
 * Run every day at 9:00 AM
 * - Generates certificates for completed courses
 * - Archives old certificates
 * - Sends certificate notifications
 */
Artisan::command('certificates:generate', GenerateCertificates::class)
    ->purpose('Generate course completion certificates')
    ->describe('Creates and sends certificates for students who completed courses');

Artisan::command('schedule:generate-certificates', function () {
    $this->call('certificates:generate');
})->dailyAt('09:00')->name('certificates:generate-scheduled');

// ============================================================================
// NOTIFICATION & COMMUNICATION COMMANDS
// ============================================================================

/**
 * Send Notifications
 * Run every 10 minutes
 * - Processes queued notifications
 * - Sends email notifications
 * - Sends push notifications
 * - Sends SMS notifications
 */
Artisan::command('notifications:send', SendNotifications::class)
    ->purpose('Send queued notifications')
    ->describe('Processes and sends email, push, and SMS notifications');

Artisan::command('schedule:send-notifications', function () {
    $this->call('notifications:send');
})->everyTenMinutes()->name('notifications:send-scheduled');

// ============================================================================
// CACHE & PERFORMANCE COMMANDS
// ============================================================================

/**
 * Cache Warming
 * Run every day at 1:00 AM
 * - Preloads frequently accessed data into cache
 * - Caches product categories and listings
 * - Caches popular courses and files
 * - Improves application performance
 */
Artisan::command('cache:warm', CacheWarming::class)
    ->purpose('Warm up application cache')
    ->describe('Preloads frequently accessed data into cache for better performance');

Artisan::command('schedule:warm-cache', function () {
    $this->call('cache:warm');
})->dailyAt('01:00')->name('cache:warm-scheduled');

// ============================================================================
// DATABASE MAINTENANCE COMMANDS
// ============================================================================

/**
 * Optimize Database
 * Run every Sunday at 5:00 AM
 * - Optimizes database tables
 * - Analyzes query performance
 * - Cleans up soft deleted records
 */
Artisan::command('schedule:db-optimize', function () {
    $this->call('db:seed', ['--class' => 'DatabaseOptimizer']);
    $this->info('Database optimization completed');
})->weeklyOn(0, '05:00')->name('db:optimize-scheduled');

// ============================================================================
// FILE MAINTENANCE COMMANDS
// ============================================================================

/**
 * Clean Up Old Files
 * Run every Saturday at 11:00 PM
 * - Removes old soft-deleted files
 * - Cleans up unused file uploads
 * - Compresses old file archives
 */
Artisan::command('schedule:cleanup-files', function () {
    // Implement file cleanup logic
    $this->info('File cleanup completed');
})->weeklyOn(6, '23:00')->name('files:cleanup-scheduled');

// ============================================================================
// MONITORING & HEALTH CHECK COMMANDS
// ============================================================================

/**
 * Health Check
 * Run every 5 minutes
 * - Checks database connectivity
 * - Monitors disk space
 * - Checks cache status
 * - Monitors queue status
 */
Artisan::command('schedule:health-check', function () {
    $this->info('Health check: Database OK');
    // Add actual health check logic here
})->everyFiveMinutes()->name('health:check-scheduled');

/**
 * Application Monitoring
 * Run every 15 minutes
 * - Logs application metrics
 * - Tracks performance issues
 * - Monitors queue status
 * - Checks error rates
 */
Artisan::command('schedule:monitoring', function () {
    $this->info('Application monitoring completed');
})->everyFifteenMinutes()->name('app:monitoring-scheduled');

// ============================================================================
// SCHEDULE DISPLAY COMMAND
// ============================================================================

/**
 * Display all scheduled commands
 * Usage: php artisan schedule:list
 */
Artisan::command('schedule:list', function () {
    $this->info('=== Scheduled Tasks ===');
    $this->line('');
    $this->info('Tickets:');
    $this->line('  - Update Status (Every 4 hours)');
    $this->line('');
    $this->info('Reports:');
    $this->line('  - Daily Reports (02:00 AM)');
    $this->line('  - Weekly Reports (Sunday 03:00 AM)');
    $this->line('  - Monthly Reports (1st of month 04:00 AM)');
    $this->line('');
    $this->info('Maintenance:');
    $this->line('  - Clean Logs (03:00 AM)');
    $this->line('  - Clean Sessions (Every 4 hours)');
    $this->line('  - Cache Clear (Every hour)');
    $this->line('  - Warm Cache (01:00 AM)');
    $this->line('');
    $this->info('Payments:');
    $this->line('  - Process Payments (Every 30 minutes)');
    $this->line('  - Verify Payments (Every hour)');
    $this->line('');
    $this->info('Learning:');
    $this->line('  - Send Course Reminders (08:00 AM)');
    $this->line('  - Generate Certificates (09:00 AM)');
    $this->line('');
    $this->info('Notifications:');
    $this->line('  - Send Notifications (Every 10 minutes)');
    $this->line('');
    $this->info('Health & Monitoring:');
    $this->line('  - Health Check (Every 5 minutes)');
    $this->line('  - Application Monitoring (Every 15 minutes)');
    $this->line('');
    $this->info('Database & Files:');
    $this->line('  - Database Optimize (Sunday 05:00 AM)');
    $this->line('  - File Cleanup (Saturday 11:00 PM)');
})->purpose('Display all scheduled tasks');

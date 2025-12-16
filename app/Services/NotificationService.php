<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Models\File;
use App\Models\Course;
use App\Models\NotificationLog;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationService
{
    /**
     * Send payment confirmation notification
     *
     * @param Order $order
     * @return bool
     */
    public function sendPaymentConfirmation(Order $order): bool
    {
        try {
            $user = $order->user;

            $notificationData = [
                'type' => 'payment_confirmation',
                'order' => $order,
                'user' => $user,
            ];

            // Send email notification
            $this->sendEmailNotification(
                $user->email,
                'Payment Confirmation',
                'emails.payment-confirmation',
                $notificationData
            );

            // Send SMS notification if available
            if ($user->phone) {
                $this->sendSmsNotification(
                    $user->phone,
                    "Payment confirmed for order #{$order->order_number}. Amount: {$order->currency} {$order->total}",
                    'payment_confirmation'
                );
            }

            $this->logNotification($user, 'payment_confirmation', 'Order', $order->id);

            return true;
        } catch (Exception $e) {
            Log::error('Payment Confirmation Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send ticket created notification
     *
     * @param Ticket $ticket
     * @return bool
     */
    public function sendTicketCreated(Ticket $ticket): bool
    {
        try {
            $user = $ticket->user;
            $service = $ticket->service;

            $notificationData = [
                'type' => 'ticket_created',
                'ticket' => $ticket,
                'user' => $user,
                'service' => $service,
            ];

            // Send email notification
            $this->sendEmailNotification(
                $user->email,
                'Ticket Created',
                'emails.ticket-created',
                $notificationData
            );

            // Send SMS notification if available
            if ($user->phone) {
                $this->sendSmsNotification(
                    $user->phone,
                    "Your ticket {$ticket->ticket_number} has been created for {$service->name}. We will process it shortly.",
                    'ticket_created'
                );
            }

            $this->logNotification($user, 'ticket_created', 'Ticket', $ticket->id);

            return true;
        } catch (Exception $e) {
            Log::error('Ticket Created Notification Error', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send ticket completed notification
     *
     * @param Ticket $ticket
     * @return bool
     */
    public function sendTicketCompleted(Ticket $ticket): bool
    {
        try {
            $user = $ticket->user;
            $service = $ticket->service;

            $notificationData = [
                'type' => 'ticket_completed',
                'ticket' => $ticket,
                'user' => $user,
                'service' => $service,
            ];

            // Send email notification
            $this->sendEmailNotification(
                $user->email,
                'Ticket Completed',
                'emails.ticket-completed',
                $notificationData
            );

            // Send SMS notification if available
            if ($user->phone) {
                $this->sendSmsNotification(
                    $user->phone,
                    "Your ticket {$ticket->ticket_number} has been completed! Check your email for details.",
                    'ticket_completed'
                );
            }

            $this->logNotification($user, 'ticket_completed', 'Ticket', $ticket->id);

            return true;
        } catch (Exception $e) {
            Log::error('Ticket Completed Notification Error', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send file unlocked notification
     *
     * @param User $user
     * @param File $file
     * @return bool
     */
    public function sendFileUnlocked(User $user, File $file): bool
    {
        try {
            $notificationData = [
                'type' => 'file_unlocked',
                'file' => $file,
                'user' => $user,
            ];

            // Send email notification
            $this->sendEmailNotification(
                $user->email,
                'File Available for Download',
                'emails.file-unlocked',
                $notificationData
            );

            // Send SMS notification if available
            if ($user->phone) {
                $this->sendSmsNotification(
                    $user->phone,
                    "Your file '{$file->filename}' is now available for download!",
                    'file_unlocked'
                );
            }

            $this->logNotification($user, 'file_unlocked', 'File', $file->id);

            return true;
        } catch (Exception $e) {
            Log::error('File Unlocked Notification Error', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send course enrolled notification
     *
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function sendCourseEnrolled(User $user, Course $course): bool
    {
        try {
            $notificationData = [
                'type' => 'course_enrolled',
                'course' => $course,
                'user' => $user,
            ];

            // Send email notification
            $this->sendEmailNotification(
                $user->email,
                'Welcome to the Course',
                'emails.course-enrolled',
                $notificationData
            );

            // Send SMS notification if available
            if ($user->phone) {
                $this->sendSmsNotification(
                    $user->phone,
                    "Welcome to '{$course->name}'! Your course access has been activated.",
                    'course_enrolled'
                );
            }

            $this->logNotification($user, 'course_enrolled', 'Course', $course->id);

            return true;
        } catch (Exception $e) {
            Log::error('Course Enrolled Notification Error', [
                'course_id' => $course->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send email notification via job queue
     *
     * @param string $email
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return void
     */
    protected function sendEmailNotification(string $email, string $subject, string $view, array $data): void
    {
        try {
            SendEmailNotificationJob::dispatch($email, $subject, $view, $data);
            Log::info('Email notification queued', ['email' => $email, 'subject' => $subject]);
        } catch (Exception $e) {
            Log::error('Email notification queue error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS notification via job queue
     *
     * @param string $phone
     * @param string $message
     * @param string $type
     * @return void
     */
    protected function sendSmsNotification(string $phone, string $message, string $type): void
    {
        try {
            SendSmsNotificationJob::dispatch($phone, $message, $type);
            Log::info('SMS notification queued', ['phone' => $phone, 'type' => $type]);
        } catch (Exception $e) {
            Log::error('SMS notification queue error', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log notification in database
     *
     * @param User $user
     * @param string $type
     * @param string $notifiable_type
     * @param int $notifiable_id
     * @return bool
     */
    protected function logNotification(User $user, string $type, string $notifiable_type, int $notifiable_id): bool
    {
        try {
            NotificationLog::create([
                'user_id' => $user->id,
                'type' => $type,
                'notifiable_type' => $notifiable_type,
                'notifiable_id' => $notifiable_id,
                'sent_at' => now(),
            ]);
            return true;
        } catch (Exception $e) {
            Log::error('Notification Log Error', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send custom notification
     *
     * @param User $user
     * @param string $subject
     * @param string $message
     * @param string $type
     * @return bool
     */
    public function sendCustomNotification(User $user, string $subject, string $message, string $type = 'custom'): bool
    {
        try {
            // Send email
            if ($user->email) {
                $this->sendEmailNotification(
                    $user->email,
                    $subject,
                    'emails.custom-notification',
                    [
                        'type' => $type,
                        'subject' => $subject,
                        'message' => $message,
                        'user' => $user,
                    ]
                );
            }

            // Send SMS
            if ($user->phone && strlen($message) <= 160) {
                $this->sendSmsNotification($user->phone, $message, $type);
            }

            $this->logNotification($user, $type, 'Custom', 0);

            return true;
        } catch (Exception $e) {
            Log::error('Custom Notification Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

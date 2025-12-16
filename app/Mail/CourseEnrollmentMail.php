<?php

namespace App\Mail;

use App\Models\CourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseEnrollmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $enrollment;

    /**
     * Create a new message instance.
     */
    public function __construct(CourseEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Course Enrollment Confirmed - ' . $this->enrollment->course->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.course.enrollment',
            with: [
                'enrollment' => $this->enrollment,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

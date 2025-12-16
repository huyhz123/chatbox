<?php

namespace App\Mail;

use App\Models\FileDownload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FileDownloadLink extends Mailable
{
    use Queueable, SerializesModels;

    public $download;

    /**
     * Create a new message instance.
     */
    public function __construct(FileDownload $download)
    {
        $this->download = $download;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Download Link - ' . $this->download->file->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.file.download-link',
            with: [
                'download' => $this->download,
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

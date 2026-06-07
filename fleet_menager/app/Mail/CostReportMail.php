<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CostReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $csvContent,
        public string $fileName = 'raport.csv'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Raport Kosztów Floty',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.report',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->csvContent, $this->fileName)
                ->withMime('text/csv'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mail wysyłany do fleet managerów po zgłoszeniu nowego incydentu
 * przez kierowcę. W developmencie driver to 'log' - mail trafia
 * do storage/logs/laravel.log (widać że poszedł).
 */
class IncidentReported extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Fleet] ' . __('Nowe zgłoszenie') . ' #' . $this->event->id
                     . ' - ' . ($this->event->vehicle?->plate_number ?? '?'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incident',
            with: [
                'event'   => $this->event,
                'vehicle' => $this->event->vehicle,
                'driver'  => $this->event->reporter,
            ],
        );
    }
}

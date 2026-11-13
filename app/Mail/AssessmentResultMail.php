<?php

namespace App\Mail;

use App\Models\AssessmentAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssessmentResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AssessmentAttempt $attempt,
        public string $pdfContent,
        public string $pdfFilename
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hasil Asesmen PELITA - '.$this->attempt->assessment->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.assessment-result',
            with: ['attempt' => $this->attempt],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}

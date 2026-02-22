<?php

namespace App\Mail\Intervention;

use App\Models\Intervention\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ReportSignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Intervention $intervention
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Votre rapport d'intervention - {$this->intervention->reference}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.intervention.report-signed',
            with: [
                'customerName' => $this->intervention->customer->display_name,
                'label' => $this->intervention->label,
                'date' => $this->intervention->completed_at?->format('d/m/Y'),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->intervention->pdf_path && Storage::disk('public')->exists($this->intervention->pdf_path)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->intervention->pdf_path)
                ->as("Rapport_Intervention_{$this->intervention->reference}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}

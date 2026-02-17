<?php

namespace App\Mail\Articles;

use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DormantStockReportMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Collection $articles,
        public Tenants $tenant,
        public User $recipient
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $month = now()->translatedFormat('F Y');

        return new Envelope(
            subject: "Accusé de réception de votre rapport de stock dormant - {$month}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.articles.dormant-report',
            with: [
                'articles' => $this->articles,
                'recipient_name' => mb_strtoupper($this->recipient->name),
                'tenant_name' => $this->tenant->name,
                'report_number' => 'BT-'.$this->tenant->id.'-'.now()->format('YmdHi'),
                'month_year' => now()->translatedFormat('F Y'),
                'date_full' => now()->translatedFormat('d F Y à H\hi'),
                'date' => now()->format('d/m/Y'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

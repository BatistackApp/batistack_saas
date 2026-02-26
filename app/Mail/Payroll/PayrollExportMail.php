<?php

namespace App\Mail\Payroll;

use App\Models\Payroll\PayrollPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayrollExportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public PayrollPeriod $period,
        public string $filePath
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->period->tenant->name.' - Export Comptable Paie - Période : {$this->period->name}',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payroll.export',
            with: [
                'periodName' => $this->period->name,
                'startDate' => $this->period->start_date->format('d/m/Y'),
                'endDate' => $this->period->end_date->format('d/m/Y'),
                'report_number' => $this->period->start_date->format('dmY').$this->period->end_date->format('dmY'),
                'count' => $this->period->payslips->count(),
                'totalNet' => $this->period->payslips()->sum('net_to_pay'),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('public', $this->filePath)
                ->as("Export_Paie_{$this->period->id}.csv")
                ->withMime('text/csv'),
        ];
    }
}

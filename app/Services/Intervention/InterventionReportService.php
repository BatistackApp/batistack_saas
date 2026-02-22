<?php

namespace App\Services\Intervention;

use App\Models\Intervention\Intervention;
use App\Services\Core\DocumentManagementService;

class InterventionReportService
{
    public function __construct(
        protected DocumentManagementService $documentService,
    ) {}

    /**
     * Génère le Bon d'Intervention signé en PDF.
     */
    public function generatePdfReport(Intervention $intervention): string
    {
        return $this->documentService->generatePdf(
            model: $intervention,
            view: 'pdf.interventions.report',
            type: 'interventions'
        );
    }

    public function sendToCustomer(Intervention $intervention): void
    {
        $pdfPath = $intervention->pdfPath;

        if (! $pdfPath) {
            $pdfPath = $this->generatePdfReport($intervention);
        }

        // Logique d'envoi d'email via un Mailable Laravel
        /*
        Mail::to($intervention->customer->email)
            ->send(new \App\Mail\Intervention\ReportSignedMail($intervention));
        */
    }
}

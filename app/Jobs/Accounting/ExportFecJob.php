<?php

namespace App\Jobs\Accounting;

use App\Services\Accounting\FecExportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportFecJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Carbon $startDate,
        private Carbon $endDate,
    ) {}

    public function handle(FecExportService $service): void
    {
        // Valider avant export
        $errors = $service->validate($this->startDate, $this->endDate);

        if (!empty($errors)) {
            \Illuminate\Support\Facades\Log::warning('FEC Export Errors', $errors);
            throw new \RuntimeException('FEC export validation failed');
        }

        // Générer l'export
        $filePath = $service->export($this->startDate, $this->endDate);

        // Notifier l'utilisateur que le fichier est prêt
        $user = auth()->user();
        $user->notify(new \App\Notifications\Accounting\FecExportReadyNotification($filePath));
    }
}

<?php

namespace App\Jobs\Bim;

use App\Enums\Bim\BimModelStatus;
use App\Exceptions\Bim\BimModuleException;
use App\Models\Bim\BimModel;
use App\Models\User;
use App\Notifications\Bim\BimModelErrorNotification;
use App\Notifications\Bim\BimModelReadyNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Log;

class ProcessBimModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private readonly BimModel $model) {}

    public function handle(): void
    {
        try {
            // 1. Passage en mode processing
            $this->model->update(['status' => BimModelStatus::PROCESSING]);

            // 2. Appel au service de parsing (Simulation ou appel Wasm/Node)
            // Dans un flux réel, le service jetterait une ModelParsingException en cas d'erreur
            // $bimService->ingestExtractedObjects($this->model, $extractedData);

            // 3. Finalisation
            $this->model->update(['status' => BimModelStatus::READY]);

            // 4. Notification des responsables du projet
            $projectManagers = User::permission('view-bim')->get();
            Notification::send($projectManagers, new BimModelReadyNotification($this->model));

        } catch (BimModuleException $e) {
            // Erreur spécifique au métier BIM (parsing, version...)
            $this->handleFailure($e->getMessage());
            throw $e;
        } catch (Exception $e) {
            // Erreur système générique
            Log::error('Erreur système lors du traitement BIM: '.$e->getMessage());
            $this->handleFailure('Une erreur technique inattendue est survenue.');
            throw $e;
        }
    }

    /**
     * Centralise la gestion d'échec du job.
     */
    protected function handleFailure(string $errorMessage): void
    {
        $this->model->update(['status' => BimModelStatus::ERROR]);

        $projectManagers = User::permission('view-bim')->get();
        Notification::send($projectManagers, new BimModelErrorNotification($this->model, $errorMessage));
    }
}

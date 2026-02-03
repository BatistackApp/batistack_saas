<?php

namespace App\Jobs\Commerce;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SyncProjectFinancialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Model $model) {}

    public function handle(): void
    {
        $project = $this->model->project;
        if (! $project) {
            return;
        }

        Log::info("Recalcul des indicateurs financiers pour le projet : {$project->name}");

        // Logique de calcul :
        // - Somme des Devis Acceptés -> CA Prévisionnel
        // - Somme des BC Validés -> Dépenses Engagées
        // - Somme des Situations Validées -> CA Réalisé (Facturé)

        // Ces calculs alimentent le tableau de bord "Marge à terminaison"
    }
}

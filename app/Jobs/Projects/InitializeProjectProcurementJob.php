<?php

namespace App\Jobs\Projects;

use App\Models\Projects\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class InitializeProjectProcurementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Project $project) {}

    public function handle(): void
    {
        Log::info("Début de la préparation des approvisionnements pour le projet : {$this->project->code_project}");

        // 1. Analyse du budget matériaux et pré-réservation
        $this->analyzeMaterialRequirements();

        // 2. Identification des besoins en sous-traitance critique
        $this->identifySubcontractingNeeds();

        // 3. Notification au service Achats pour les articles à long délai de livraison
        $this->notifyProcurementDepartment();

        Log::info("Préparation des approvisionnements terminée avec succès pour le projet : {$this->project->code_project}");
    }

    /**
     * Analyse les budgets alloués pour identifier les masses de matériaux à commander.
     */
    protected function analyzeMaterialRequirements(): void
    {
        $materialBudget = (float) $this->project->budget_materials;

        if ($materialBudget <= 0) {
            Log::warning("Alerte Achat : Aucun budget matériaux défini pour le projet {$this->project->code_project}");

            return;
        }

        // Simule la création d'une liste de réservation de stock
        Log::info("Réservation de principe créée pour un volume de {$materialBudget} € HT de fournitures.");
    }

    /**
     * Vérifie si des lots de sous-traitance sont prévus et prépare les consultations.
     */
    protected function identifySubcontractingNeeds(): void
    {
        $subBudget = (float) $this->project->budget_subcontracting;

        if ($subBudget > 0) {
            Log::info("Besoins en sous-traitance identifiés : {$subBudget} € HT. Préparation des dossiers de consultation (DC4).");
        }
    }

    /**
     * Alerte les acheteurs sur les besoins spécifiques ou les délais critiques.
     */
    protected function notifyProcurementDepartment(): void
    {
        // Dans une version complète, ceci enverrait une notification ou créerait une tâche dans le module Stock
        $siteOverheads = (float) $this->project->budget_site_overheads;

        if ($siteOverheads > 5000) {
            Log::info("Alerte Logistique : Budget d'installation important ({$siteOverheads} €). Planification des livraisons de bases vie et grues requise.");
        }
    }
}

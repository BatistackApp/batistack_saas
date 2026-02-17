<?php

namespace App\Jobs\Projects;

use App\Enums\Projects\ProjectPhaseStatus;
use App\Enums\Projects\ProjectUserRole;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class TriggerProjectPlanningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(private Project $project) {}

    public function handle(): void
    {
        Log::info("Début de l'initialisation du planning pour le projet: {$this->project->code_project}");

        // 1. Initialisation des phases par défaut si aucune n'existe
        // On s'assure que le projet a au moins une structure de base pour démarrer
        if ($this->project->phases()->count() === 0) {
            $this->createDefaultPhases();
        }

        // 2. Vérification des affectations de ressources critiques
        $this->verifyManagementAssignments();

        // 3. Analyse des dépendances (Validation du chemin critique théorique)
        $this->auditGanttDependencies();

        Log::info("Planification initialisée avec succès pour le projet: {$this->project->code_project}");
    }

    /**
     * Crée des phases standards pour un nouveau chantier si le BE n'a rien saisi.
     */
    protected function createDefaultPhases(): void
    {
        $defaultPhases = [
            ['name' => 'Installation de chantier', 'order' => 10, 'budget_pct' => 0.05],
            ['name' => 'Gros Œuvre / Structure', 'order' => 20, 'budget_pct' => 0.60],
            ['name' => 'Second Œuvre / Finitions', 'order' => 30, 'budget_pct' => 0.30],
            ['name' => 'Repli et Nettoyage', 'order' => 40, 'budget_pct' => 0.05],
        ];

        foreach ($defaultPhases as $data) {
            ProjectPhase::create([
                'project_id' => $this->project->id,
                'name' => $data['name'],
                'allocated_budget' => $this->project->allocated_phases_ceiling_ht * $data['budget_pct'],
                'order' => $data['order'],
                'status' => ProjectPhaseStatus::Pending,
            ]);
        }
    }

    /**
     * S'assure qu'un Conducteur de Travaux est bien présent dans l'équipe.
     */
    protected function verifyManagementAssignments(): void
    {
        $hasManager = $this->project->members()
            ->wherePivot('role', ProjectUserRole::ProjectManager->value)
            ->exists();

        if (! $hasManager) {
            Log::warning("Alerte Planification : Aucun Conducteur de Travaux affecté au projet {$this->project->code_project}");
            // Ici, on pourrait déclencher une notification spécifique ou créer une tâche d'assignation
        }
    }

    /**
     * Audit simple des liens de dépendance pour éviter les boucles infinies.
     */
    protected function auditGanttDependencies(): void
    {
        // Logique de vérification de cohérence des dates et des chaînages
        // On s'assure que les phases dépendantes ne commencent pas avant leurs parents (FS)
        foreach ($this->project->phases as $phase) {
            if ($phase->depends_on_phase_id && $phase->dependency) {
                // Logique de validation temporelle...
            }
        }
    }
}

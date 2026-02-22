#### ** [ ] Recommandation 1 : Affiner l'Imputation Analytique des Frais**

*   **Observation :** Le service `ChantierImputationService` est présent, ce qui est une excellente base. Le modèle `ExpenseItem` a un `project_id`, ce qui permet de lier un frais à un chantier.
*   **Point de vigilance métier :** Dans le BTP, imputer un coût au "chantier" global n'est souvent pas suffisant. Un conducteur de travaux a besoin de savoir si un frais (ex: "achat de petit outillage en urgence") concerne le lot "Gros Œuvre" ou "Second Œuvre".
*   **Recommandation Technique :**
    *   Ajouter un champ `phase_id` (ou `zone_id`, `lot_id` selon votre terminologie) nullable au modèle `ExpenseItem`.
    *   Lors de la saisie d'une ligne de frais, si un `project_id` est sélectionné, proposer une liste déroulante des phases/lots de ce chantier.
    *   Enrichir le `ProcessChantierImputationJob` pour qu'il ventile le coût non seulement sur le chantier mais aussi sur la phase concernée.
    *   **Bénéfice :** Vous offrirez une rentabilité par lot beaucoup plus précise, ce qui est un argument de vente majeur pour les conducteurs de travaux et contrôleurs de gestion.

#### ** [ ] Recommandation 2 : Gérer la Complexité des Indemnités Kilométriques (IK)**

*   **Observation :** La logique actuelle (`ExpenseCalculationService->calculateKm`) prend une distance et un taux. La catégorie de frais (`ExpenseCategory`) a une option `requires_distance`. C'est un bon début.
*   **Point de vigilance métier :** Le taux des IK n'est pas fixe. En France, il dépend du barème URSSAF qui varie selon la puissance fiscale du véhicule du salarié et le nombre de kilomètres parcourus dans l'année. Un taux fixe est une simplification qui peut engendrer des redressements.
*   **Recommandation Technique :**
    *   Ne pas stocker un "taux" fixe dans le service de calcul.
    *   Le calcul des IK devrait plutôt être une responsabilité du **module Paie/RH**. Le service actuel pourrait appeler un futur `PayrollService->getMileageRateForEmployee(User $user, float $distance)`.
    *   Ce service irait chercher la puissance fiscale du véhicule du salarié (potentiellement dans le **module Flottes**) et appliquerait la bonne tranche du barème.
    *   **Bénéfice :** Vous assurez une conformité légale et une automatisation complète, évitant au salarié ou au comptable de devoir calculer manuellement ce montant complexe.

#### ** [ ] Recommandation 3 : Introduire la Notion de "Frais Refacturable"**

*   **Observation :** Le système gère bien les frais comme des coûts internes.
*   **Point de vigilance métier :** Il est fréquent qu'un chef de chantier engage une dépense qui doit être refacturée au client final (ex: une modification demandée oralement, un achat spécifique non prévu au devis). Ces frais ne doivent pas seulement impacter la marge, mais aussi générer du chiffre d'affaires.
*   **Recommandation Technique :**
    *   Ajouter un booléen `is_billable` sur le modèle `ExpenseItem`.
    *   Lors de la validation de la note de frais, si un item est marqué comme "refacturable", générer une alerte ou une tâche pour le service facturation.
    *   Idéalement, lors de la création de la prochaine "Situation de travaux" (Module Commerce), le système devrait proposer d'inclure automatiquement ces frais.
    *   **Bénéfice :** Vous évitez les "oublis" de facturation, qui sont une perte sèche pour l'entreprise. C'est un lien direct entre le terrain et la facturation.

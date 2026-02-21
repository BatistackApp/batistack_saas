### **Liste de Recommandations Professionnelles - Module Locations**

#### **Axe 1 : Affiner le Cycle de Vie et le Workflow du Contrat**

1.  **Instaurer la notion d' "Appel de Reprise" (Off-Hire)**
    *   **Constat :** Dans le BTP, la fin d'une location est initiée par un appel du chef de chantier au loueur pour qu'il vienne récupérer le matériel. La facturation s'arrête à la date de cet appel, même si le matériel est physiquement repris 1 ou 2 jours plus tard. Le statut `ENDED` actuel est ambigu : signifie-t-il "appel de reprise fait" ou "matériel physiquement parti" ?
    *   **Recommandation :**
        *   Dans le modèle `RentalContract`, ajoutez un champ `off_hire_requested_at` (timestamp, nullable).
        *   Modifiez le `RentalWorkflowService` : au lieu de passer directement à `ENDED`, introduisez un état intermédiaire `OFF_HIRE` (ou "En attente de reprise"). Le passage à cet état renseignerait `off_hire_requested_at`.
        *   Le `RentalCalculationService` devra utiliser `off_hire_requested_at` comme date de fin de calcul pour la facturation, et non `actual_return_at`. Cela évitera de nombreux litiges avec les fournisseurs.

2.  **Gérer les Prolongations de Contrat**
    *   **Constat :** Une date de fin prévisionnelle (`end_date_planned`) est rarement respectée. Les chantiers subissent des aléas (météo, retards) qui nécessitent de prolonger la location. Le système actuel ne semble pas formaliser ce processus.
    *   **Recommandation :**
        *   Créez une route et une méthode (par exemple dans `RentalContractController`) dédiée à la prolongation.
        *   Cette action devrait créer un historique des prolongations (une table `rental_contract_extensions` liée au contrat) pour tracer qui a demandé la prolongation, quand, et jusqu'à quelle nouvelle date. C'est crucial pour le suivi budgétaire.
        *   L'alerte de fin de contrat (`CheckExpiringRentalsCommand`) devrait proposer deux actions claires : "Organiser la reprise" ou "Prolonger le contrat".

#### **Axe 2 : Enrichir la Précision des Coûts et de l'Usage**

3.  **Intégrer le Suivi par Horamètre (Heures Moteur)**
    *   **Constat :** Pour les engins (pelles, nacelles, etc.), la location est très souvent facturée à la fois sur la durée **ET** sur un forfait d'heures d'utilisation (ex: 8h/jour). Tout dépassement est facturé en supplément. Le système actuel ne suit que la durée.
    *   **Recommandation :**
        *   Dans le modèle `RentalInspection`, ajoutez les champs `engine_hours_start` et `engine_hours_end` (decimal).
        *   L'état des lieux d'entrée (`Entry`) et de sortie (`Exit`) doit obligatoirement inclure le relevé du compteur horaire.
        *   Le `RentalCalculationService` doit intégrer une logique de calcul des heures supplémentaires si le total des heures dépasse le forfait prévu par le contrat (à ajouter dans `RentalItem`).

4.  **Isoler les Coûts de Transport et Frais Annexes**
    *   **Constat :** Le coût d'une location inclut presque toujours des frais de livraison et de reprise. Ces coûts fixes sont importants et doivent être imputés au chantier, mais ils ne sont pas liés à la durée de location. Ils ne peuvent donc pas figurer dans `RentalItem`.
    *   **Recommandation :**
        *   Ajoutez des champs sur le modèle `RentalContract` : `delivery_cost_ht` et `pickup_cost_ht`.
        *   Le `RentalCostImputationService` doit imputer ces coûts en "one-shot" dès l'activation du contrat (`startRental`), et non quotidiennement.

#### **Axe 3 : Renforcer la Fiabilité Administrative et Opérationnelle**

5.  **Stocker la Référence Contrat du Fournisseur**
    *   **Constat :** L'observer `RentalContractObserver` génère une référence interne (`LOC-2024-XXXX`), ce qui est parfait. Cependant, pour rapprocher la facture du fournisseur, il est indispensable de connaître **leur** numéro de contrat.
    *   **Recommandation :**
        *   Ajoutez un champ `provider_reference` (string, nullable) au modèle `RentalContract`.
        *   Rendez ce champ bien visible et quasi-obligatoire à la saisie pour le gestionnaire. Cela divisera par deux le temps passé au rapprochement de factures.

6.  **Enrichir les États des Lieux (Inspections)**
    *   **Constat :** Les photos sont une excellente chose. Dans la pratique, un état des lieux BTP doit être contradictoire pour être valable.
    *   **Recommandation :**
        *   Dans le modèle `RentalInspection`, ajoutez un champ `client_signature` (text, pour stocker une image base64) et `provider_signature` (signature du livreur).
        *   L'interface mobile doit permettre au chef de chantier et au livreur de signer directement sur l'écran pour valider l'état des lieux. Ce document devient alors une preuve juridique en cas de litige sur des dégradations.

7.  **Gérer le Transfert Inter-Chantiers**
    *   **Constat :** Il est très fréquent de louer un matériel pour le chantier A, puis de le déplacer directement sur le chantier B sans le retourner au loueur pour optimiser les coûts de transport. Le modèle actuel lie un contrat à un seul `project_id`.
    *   **Recommandation :**
        *   Créez une fonctionnalité "Transférer le matériel". Plutôt que de modifier le `project_id` du contrat initial (ce qui fausserait l'historique des coûts), cette action devrait :
            1.  Clôturer l'imputation sur le projet A à la date du transfert.
            2.  Créer une nouvelle "période d'affectation" (`rental_assignments` par exemple) liée au projet B, qui reprend l'imputation des coûts à partir de cette date.
        *   Le contrat de location reste unique, mais son coût est ventilé dynamiquement entre plusieurs chantiers.

---

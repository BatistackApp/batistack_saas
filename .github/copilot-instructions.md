<laravel-boost-guidelines>
=== .ai/context-development rules ===

# Etape de développement
## Backend Developpement
Pour chaque module:

- Etape 1: Définir le contexte du module
- Etape 2: Définir les migrations, Enums et Modèle Eloquents propre au module
- Etape 3: Définir La porté technique du module en s'appuyant sur des exemples concret 
- Etape 4: Définir les services dont le module aura besoin dans le namespace App/Service/{NomDuModule}
- Etape 5: Automatisation du module par la mise en place:
    - Observer
    - Jobs
    - Notifications
    - Schedules (Si Besoin)
- Etape 6: Etablissement des Tests Unitaires/Features en corrélation avec le Module

=== .ai/module-3d-vision rules ===

Module 3D Vision (BIM & Visualisation) - Batistack (Add-on)

1. Responsabilités du Module

Ce module permet l'intégration, la visualisation et l'interaction avec les maquettes numériques (BIM) au format IFC. Sa mission est de lier les éléments géométriques de la maquette aux données métier de l'ERP (lignes de devis, articles en stock, avancement de chantier) pour offrir une immersion totale dans le projet.

2. Entités et Structure de Données

Maquettes BIM :

chantier_id, nom_maquette, version.

file_path (Lien vers le fichier .ifc ou .rvt converti stocké sur S3).

metadata (JSON : Informations globales sur le projet BIM).

Objets BIM (Éléments de la maquette) :

guid (Global Unique Identifier de l'élément dans le fichier IFC).

ifc_type (ex: IfcWall, IfcWindow, IfcSlab).

label (Nom de l'élément).

Liaisons Métier (BIM Mapping) :

Table pivot liant un guid d'objet à :

Une ligne de Devis.

Un Article de stock.

Une Tâche de planning.

3. Fonctionnalités de Visualisation

Viewer Interactif : Visualisation fluide en 3D (Rotation, Zoom, Pan) directement dans le navigateur sans plugin.

Explosion & Coupes : Possibilité de masquer des couches (ex: masquer les murs pour voir les réseaux électriques) ou de réaliser des coupes transversales.

Sélection Intelligente : Cliquer sur un mur dans la 3D affiche instantanément son coût au mètre carré, son avancement et les matériaux nécessaires issus de l'ERP.

Code Couleur d'Avancement : Coloration automatique de la maquette en fonction du statut du chantier (ex: Vert = Terminé, Orange = En cours, Rouge = Retard).

4. Workflow BIM-to-Data

Import : Téléchargement du fichier IFC dans le module GED.

Parsing : Extraction des GUIDs et de la structure spatiale du fichier.

Appairage : Liaison des objets 3D avec les ouvrages du module Articles.

Exploitation : Utilisation de la 3D pour les réunions de chantier, les métrés automatiques et le suivi de production.

5. Logique Technique & Packages (Laravel 12.x)

Moteur de Rendu : Utilisation de Three.js via la bibliothèque spécialisée IFC.js (ou son successeur That Open Engine) pour le rendu performant des fichiers IFC.

Conversion Cloud : Utilisation de Workers ou de Micro-services pour convertir les fichiers lourds en formats optimisés pour le web (ex: glTF ou fragments).

API REST : Laravel sert les métadonnées et les liens métier via des endpoints JSON hautement optimisés.

Stockage : Gestion des fichiers volumineux via Laravel Storage sur S3 avec mise en cache locale des fragments les plus consultés.

6. Intégrations Multi-modules

Chantiers : Visualisation de l'avancement physique directement sur la maquette.

Commerce : Génération de devis par extraction des quantités depuis la maquette (Métré automatique).

Articles & Stocks : Localisation visuelle des matériaux nécessaires par zone de chantier.

GED : Source des fichiers sources et archivage des différentes révisions de la maquette.

Interventions : Localisation précise des équipements à maintenir (ex: vanne d'arrêt, compteur) pour les techniciens SAV.

=== .ai/module-articles-stocks rules ===

Module Articles & Stocks - Batistack (Core)

1. Responsabilités du Module

Ce module gère l'ensemble du référentiel matériel et technique de l'entreprise. Il assure la disponibilité des ressources, la gestion des prix de revient et le suivi physique des marchandises à travers plusieurs lieux de stockage, incluant les procédures de contrôle de fiabilité et d'identification par capture optique.

2. Entités et Structure de Données

Articles :

reference (SKU unique), designation, unite.

barcode (Code-barres EAN/UPC pour le référencement catalogue).

qr_code_base (Identifiant unique pour étiquetage interne).

famille_id, prix_achat_ht, dernier_prix_achat, prix_vente_conseille.

poids, volume.

Ouvrages (Recettes / Compositions) :

Un ouvrage regroupe plusieurs articles et de la main-d'œuvre.

composition (Liste d'articles avec quantités et temps de pose estimé).

cout_revient_calcule.

Dépôts (Entrepôts) :

nom, adresse, responsable_id.

Possibilité de créer des "Dépôts Mobiles" (ex: Camion n°12).

Stocks & Unités Sérialisées :

Table pivot liant Article et Dépôt pour le vrac.

Serial Numbers (SN) : Table pour les articles suivis à l'unité (ex: outillage électroportatif, chaudières).

article_id, serial_number, status (En stock, Affecté, En maintenance, Perdu).

photo_sn (Capture de la plaque signalétique pour preuve/OCR).

Inventaires (Sessions) :

reference (INV-YYYY-XXXX).

depot_id, date_ouverture, date_cloture, statut, type.

Lignes d'Inventaire :

article_id, quantite_theorique, quantite_comptée, ecart.

3. Gestion des Mouvements

Tout flux de stock doit être tracé avec un historique immuable.

Types de mouvements : Réception fournisseur, Sortie chantier, Transfert inter-dépôts, Inventaire (ajustement), Retour chantier.

Scan-to-Process : Validation des mouvements par scan (Barcode/QR) pour limiter les erreurs de saisie.

Traçabilité : Historique complet par numéro de série pour le SAV et les garanties.

4. Fonctionnalités Clés

Identification & Traçabilité Optique :

Génération de QR Codes : Étiquetage automatique des articles ou des bacs de stockage.

Capture Photo & OCR : Reconnaissance automatique des numéros de série et codes-barres via l'appareil photo du mobile (utile pour la réception de matériel sans saisie manuelle).

Multi-Scan : Mode rafale pour scanner plusieurs articles rapidement lors d'un chargement camion.

Catalogue Multi-fournisseurs : Import de catalogues et gestion des remises.

Gestion des Ouvrages : Création de "kits" techniques.

Gestion des Inventaires : Gel du stock, saisie mobile et régularisation automatique.

Alertes de Rupture : Notifications basées sur le seuil d'alerte.

5. Logique Technique (Laravel 12.x)

Traitement d'Image : Intégration de bibliothèques côté client (ou API OCR) pour extraire le texte des photos de plaques signalétiques.

Génération de Codes : Utilisation de packages type simplesoftwareio/simple-qrcode pour la génération des étiquettes.

Atomicité : Utilisation des DB::transaction() pour les mouvements et la validation d'inventaire.

Mise à jour en temps réel : Emploi de Laravel Observers pour recalculer les quantités.

6. Intégrations Core & Add-ons

Chantiers : Imputation des sorties de stock (via scan) au coût réel du chantier.

Commerce : Déstockage automatique sur BL par scan des articles.

GPAO : Calcul des besoins (MRP) et suivi des composants par SN dans les ordres de fabrication.

Flottes : Suivi des équipements embarqués via leur numéro de série.

=== .ai/module-banque rules ===

Module Banque - Batistack (Add-on)

1. Responsabilités du Module

Ce module gère la connexion entre l'ERP et les comptes bancaires réels du tenant. Sa mission est d'automatiser la récupération des flux financiers, de faciliter le rapprochement avec les factures (ventes/achats) et de fournir une visibilité immédiate sur la trésorerie disponible.

2. Entités et Structure de Données

Comptes Bancaires (Bank Accounts) :

nom_banque, iban, bic, devise.

solde_actuel (synchronisé), date_dernier_relevé.

provider_connection_id (Lien vers l'agrégateur API).

Transactions Bancaires (Bank Transactions) :

date_transaction, libelle_bancaire, montant.

type (Débit/Crédit).

statut_rapprochement (En attente, Partiel, Rapproché, Ignoré).

Règles de Rapprochement (Matching Rules) :

pattern_libelle (ex: si contient "EDF"), compte_comptable_id, tiers_id.

Rapprochements (Matchings) :

Table pivot liant une Transaction à une ou plusieurs Factures ou Écritures Comptables.

3. Synchronisation & Open Banking

Batistack ne stocke pas les identifiants bancaires mais utilise un agrégateur tiers (Type Bridge, Plaid ou Powens/Budget Insight).

Flux de connexion : Le tenant connecte sa banque via une interface sécurisée (Webview).

Webhooks : Réception en temps réel des nouvelles transactions dès qu'elles sont traitées par la banque.

Fallback : Possibilité d'importer manuellement des relevés au format CFONB ou MT940.

4. Algorithme de Rapprochement Automatique

Le moteur de "Matching" tente d'associer les transactions selon plusieurs critères :

Référence exacte : Recherche du numéro de facture dans le libellé bancaire.

Montant & Tiers : Si le montant correspond exactement à une facture en attente d'un tiers identifié.

Règles intelligentes : Application des règles prédéfinies (ex: "Loyer" -> Compte 613).

Intelligence Artificielle (Optionnel) : Suggestion de rapprochement basée sur l'historique des transactions similaires.

5. Fonctionnalités Clés

Dashboard Trésorerie : Vue consolidée des soldes de tous les comptes avec prévisionnel basé sur les factures à échoir.

Pointage Bancaire : Interface interactive pour valider les suggestions de rapprochement en un clic.

Virements Fournisseurs (SCT) : Génération de fichiers de virement SEPA (XML) pour payer les factures d'achats directement depuis l'ERP.

Gestion des Frais Bancaires : Génération automatique des écritures de commissions et frais de tenue de compte.

6. Logique Technique & Packages (Laravel 12.x)

Agrégation API : Utilisation de packages spécifiques selon le provider choisi (ex: finto-dev/laravel-bridge ou un wrapper personnalisé Guzzle).

Traitement Asynchrone : Les imports massifs et les calculs de rapprochement sont gérés par des Jobs (ShouldQueue) pour garantir la fluidité de l'UI.

Sécurité : Chiffrement des tokens d'accès aux APIs bancaires via le système de cryptographie de Laravel.

Audit Trail : Journalisation de chaque rapprochement (qui a validé quoi et quand).

7. Intégrations Core & Add-ons

Comptabilité : Génération automatique des écritures de trésorerie (Compte 512) lors du rapprochement.

Commerce : Mise à jour immédiate du statut "Payé" des factures clients et fournisseurs.

Notes de Frais : Rapprochement des remboursements effectués aux salariés.

Flottes / Locations : Identification des prélèvements récurrents liés aux contrats de leasing ou d'assurance.

=== .ai/module-chantiers rules ===

Module Chantiers - Batistack (Core)

1. Responsabilités du Module

Ce module orchestre la vie d'un projet de construction, de l'ouverture du dossier à la réception des travaux. Sa fonction principale est la consolidation analytique : transformer chaque donnée opérationnelle (heures, achats, locations) en indicateur de rentabilité.

2. Entités et Structure de Données

Chantiers (Projets) :

code_chantier (Unique, ex: CH-2024-001).

nom_chantier, description, adresse_gps.

statut (Étude, En cours, Suspendu, Terminé, Archivé).

dates (Début prévu/réel, Fin prévisionnelle).

budget_initial (Montant global HT).

Zones / Phases :

Découpage du chantier en lots ou phases (ex: Terrassement, Gros œuvre, Second œuvre) pour un suivi budgétaire fin.

Affectations :

conducteur_travaux_id (User).

chef_chantier_id (User).

equipe_ids (Salariés affectés).

3. Suivi Budgétaire et Analytique

Le système doit calculer automatiquement le "Reste à dépenser" et la "Marge à terminaison".

Coûts Main-d'œuvre : Récupération automatique des heures depuis le module Pointage multipliées par le coût horaire chargé du salarié.

Coûts Achats : Intégration des factures fournisseurs et bons de commande liés au code chantier (Module Commerce).

Coûts Locations : Imputation des frais de location de matériel (Module Locations).

Coûts Flotte : Imputation des coûts kilométriques ou horaires des engins (Module Flottes).

4. Fonctionnalités Clés

Tableau de Bord de Chantier : Vue synthétique (Réalisé vs Budget) par poste de dépense.

Journal de Chantier : Saisie quotidienne des événements (météo, incidents, avancement) avec photos (via Mobile).

Gestion des Aléas : Suivi des Travaux Supplémentaires (TS) non prévus au devis initial.

Reporting :

Génération de rapports de rentabilité (PDF/CSV).

Export des données pour les réunions de chantier.

5. Logique Technique (Laravel 12.x)

Calculs Calculés (Computed Properties) : Utilisation des Attribute de Laravel pour formater les totaux financiers en temps réel.

Actions Dédiées : CalculateChantierMarginAction pour centraliser la logique de calcul complexe et éviter la duplication.

Événements (Events) : Déclencher une alerte (Notification/Email) si le coût réel d'une phase dépasse 90% du budget alloué.

6. Intégrations Core & Add-ons

Tiers : Lien avec le Client (Maître d'ouvrage) et les Sous-traitants intervenants.

GED : Stockage centralisé des plans, PPSPS, et comptes-rendus de chantier.

Commerce : Base pour la génération des situations de travaux (Facturation à l'avancement).

3D Vision : Visualisation de la maquette BIM associée au projet (si activé).

=== .ai/module-commerce-facturation rules ===

Module Commerce & Facturation - Batistack (Core)

1. Responsabilités du Module

Ce module gère l'intégralité du cycle de vente et d'achat. Il transforme les devis en revenus et assure la conformité légale des documents financiers, tout en gérant les spécificités du BTP comme les retenues de garantie, les acomptes et les situations de travaux à l'avancement.

2. Typologie des Documents Financiers

Le système distingue quatre types de factures essentiels pour le cycle de vie d'un chantier :

Facture d'Acompte : Émise en début de projet (souvent 10% à 30%). Elle n'est pas liée à l'avancement mais constitue une avance de trésorerie.

Facture de Situation (Situation de travaux) : Émise périodiquement selon l'avancement réel du chantier. Elle calcule le montant dû en fonction du pourcentage de réalisation cumulé.

Facture Générale (Solde) : La facture finale qui clôture le marché, déduisant l'ensemble des acomptes et situations déjà versés.

Avoir : Document d'annulation totale ou partielle d'une facture validée. Indispensable car une facture validée est immuable.

3. Entités et Structure de Données

Devis (Estimations) :

reference, client_id, chantier_id.

lignes (Articles, Ouvrages, Textes libres).

statut (Brouillon, Envoyé, Accepté, Refusé).

Factures & Avoirs :

type (ACOMPTE, SITUATION, SOLDE, AVOIR).

parent_id (Lien vers la facture d'origine pour les avoirs ou le devis pour les situations).

numero_situation (Ex: Situation n°1, n°2...).

numérotation_séquentielle (Chrono unique par type ou global selon config).

Lignes de Facture (Spécifique Situation) :

devis_line_id (Référence à la ligne du devis initial).

pourcentage_avancement_cumule (Ex: 40%).

montant_precedemment_facture (Calculé pour déduction).

montant_a_facturer_periode (Résultat net).

4. Workflow de Facturation (Le Pipeline BTP)

Acceptation Devis : Le passage du devis en statut "Accepté" verrouille les lignes de prix.

Appel d'Acompte : Génération d'une facture d'acompte (souvent hors taxes au départ, régularisée sur les situations).

Cycle des Situations : * Saisie du pourcentage d'avancement par ligne de devis (ex: Gros Œuvre à 100%, Second Œuvre à 20%).

Calcul de la Situation N : (Montant Total Ligne * % Avancement Cumulé) - Montant déjà facturé en Situations N-1.

Déduction au prorata de la Facture d'Acompte initiale pour régulariser l'avance.

Avoirs de Correction : Si une erreur est détectée sur une Situation N déjà validée, le système génère un Avoir puis permet de recréer une Situation N rectificative.

5. Fonctionnalités Clés

Gestion des Situations Cumulatives : Moteur de calcul qui vérifie l'historique complet pour ne jamais facturer plus de 100% d'une ligne de devis.

Retenue de Garantie (RG) : Calcul automatique des 5% (ou autre) conservés par le client, isolés comptablement jusqu'au PV de réception.

Autoliquidation & TVA : Gestion des mentions légales en cas de sous-traitance (TVA 0% - Autoliquidation par le preneur).

Calcul des Avoirs : Génération simplifiée d'avoirs (total ou partiel) avec réouverture des droits de facturation sur le devis d'origine.

6. Logique Technique (Laravel 12.x)

Immuabilité : Utilisation d'un flag is_finalized. Une fois vrai, le modèle Invoice devient "ReadOnly" via une Policy ou un Observer.

Précision Décimale : Utilisation de bcmath ou du type decimal(15,4) pour éviter les erreurs de centimes lors des calculs de pourcentages d'avancement.

Séquençage : Service InvoiceReferenceGenerator assurant la continuité chronologique sans rupture de séquence (obligation fiscale).

Events & Analytics : Chaque validation de situation met à jour en temps réel le module Chantiers pour le calcul du reste à encaisser.

7. Intégrations Core

Chantiers : Affichage de l'avancement financier (Facturé vs Budget).

Comptabilité : Ventilation automatique en journal de ventes (avec distinction acompte/situation).

GED : Archivage du PDF de situation avec le tampon "Validé".

=== .ai/module-comptabilite rules ===

Module Comptabilité - Batistack (Core)

1. Responsabilités du Module

Ce module centralise toutes les opérations financières du tenant. Il permet une gestion hybride : automatisation des écritures issues des modules opérationnels (Ventes, Achats, Banque) et saisie manuelle pour les opérations diverses (OD). Sa mission principale est de produire des états comptables certifiés et le fichier FEC.

2. Entités et Structure de Données

Plan Comptable (Chart of Accounts) :

numero_compte (ex: 411000), libelle, type (Classe 1 à 7).

Support du Plan Comptable Général (PCG) français par défaut.

Journaux Comptables (Journals) :

code (VE, AC, BQ, OD), libelle, type.

Écritures Comptables (Accounting Entries) :

date_comptable, libelle, journal_id.

Numérotation Séquentielle Stricte : reference_unique (Format: Journal/Date/ID).

Lignes d'Écritures (Double Entrée) :

compte_id, debit, credit.

Règle : La somme des débits doit toujours être égale à la somme des crédits pour chaque écriture.

3. La Règle de Numérotation Séquentielle (Conformité)

Pour garantir l'intégrité du Grand Livre et du FEC :

Format : [CODE_JOURNAL]/[YYYYMMDD]/[INCREMENT_JOURNALIER].

Immuabilité : Une fois une écriture "clôturée" ou "validée", son numéro ne peut plus être modifié ni supprimé. Les trous dans la numérotation sont proscrits.

Verrouillage : Possibilité de clôturer des périodes (mois/année) pour interdire toute modification rétroactive.

4. Automatisation & Flux (Bridge)

Le module doit écouter les événements des autres modules pour générer les écritures :

Ventes (Commerce) : Facturation client -> Débit 411 / Crédit 706 & 4457.

Achats (Commerce/Frais) : Factures fournisseurs -> Crédit 401 / Débit 6xx & 4456.

Banque (Banque) : Transactions validées -> Écritures de trésorerie (Compte 512).

5. Reporting et Exports

Grand Livre : Vue détaillée de toutes les écritures par compte sur une période donnée.

Journaux : Impression ou export des journaux auxiliaires.

FEC (Fichier des Écritures Comptables) : Génération d'un fichier plat conforme aux exigences de l'administration fiscale (DGFIP).

Format d'export : CSV structuré pour faciliter l'intégration dans les logiciels de cabinet comptable (Sage, Cegid, MyUnisoft).

6. Logique Technique (Laravel 12.x)

Intégrité Transactionnelle : Utilisation systématique de DB::transaction() pour garantir que si une ligne d'écriture échoue, l'ensemble de l'écriture est annulée.

Calculs de Solde : Utilisation de colonnes indexées et de requêtes d'agrégation performantes pour le calcul des balances en temps réel.

Système de Clôture : Implémentation d'un "Ledger Lock" (verrou de grand livre) empêchant l'insertion de lignes sur des dates antérieures à la dernière clôture.

7. Intégrations Core

Commerce : Source principale des journaux de Ventes et Achats.

Banque : Source pour le rapprochement bancaire et les écritures de trésorerie.

RH / Paie : Intégration des écritures de paie (Salaires et charges sociales).

Notes de Frais : Intégration des remboursements et de la TVA récupérable.

=== .ai/module-core-saas rules ===

Module Core / SAAS - Batistack (Landlord)

1. Responsabilités du Module

Ce module gère la couche "Administration Globale" du SaaS. Il orchestre l'isolation des tenants, la gestion du pack de base et l'activation granulaire des modules supplémentaires.

2. Entités Principales (Schéma Landlord)

Tenants (Entreprises) :

name, slug, domain.

status (active, suspended, etc.).

Module_Catalog (Référentiel des modules) :

name (ex: "Banque", "GPAO", "Flottes").

slug (identifiant technique pour les Gates Laravel).

price_monthly, price_yearly.

is_core (boolean : true pour les 8 modules de base).

Tenant_Module (Pivot d'abonnement) :

Lien entre un Tenant et un Module.

starts_at, ends_at, status.

3. Structure de l'Offre (Modularité)

A. Le Socle "Batistack Core"

Inclus par défaut pour tout client ayant un compte actif. Il regroupe les fonctions vitales :

Tiers : Annuaires et accès espaces dédiés.

Chantiers : Suivi projet et coûts.

Articles & Stocks : Catalogue et multi-dépôts.

Commerce / Facturation : Cycle de vente BTP complet.

Comptabilité : Tenue légale et export FEC.

RH : Dossier salarié et compétences.

GED : Archivage et alertes.

Pointage : Saisie d'heures et analytique.

B. Extensions à la Carte (Add-ons)

Chaque module suivant est activable indépendamment selon les besoins du client :

Finance : Notes de Frais (OCR), Banque (Synchro API).

Opérations : GPAO (MRP), Interventions, 3D Vision (BIM).

Logistique : Flottes (Véhicules/Engins), Locations.

Social : Paie (Calcul des variables BTP).

Décisionnel : Pilotage (KPI & Dashboards).

4. Logique Multi-tenant (Laravel 12.x)

Identification : Middleware pour injecter le tenant_id et charger la liste des modules souscrits en cache (Redis).

Scope : Isolation stricte des données via Global Scopes Eloquent.

5. Système de Verrouillage (Feature Flipping)

Check via Gate : L'accès aux routes et composants des modules add-ons est protégé par une Gate : Gate::allows('access-module', 'gpao').

Dynamic Sidebar : Le menu de navigation se génère dynamiquement en fonction des modules activés dans Tenant_Module.

6. Billing & Provisioning

Paiement : Gestion des abonnements récurrents via Laravel Cashier. Un client peut ajouter ou supprimer un module add-on à tout moment (prorata automatique via Stripe).

Provisioning : À l'activation d'un module, lancement automatique des tâches d'initialisation (ex: création de dossiers spécifiques dans le stockage S3 du tenant pour le module "Flottes").

7. Audit & Support

Dashboard SuperAdmin : Vue d'ensemble des revenus par module, gestion du cycle de vie des tenants et logs d'accès.

=== .ai/module-flottes rules ===

Module Flottes - Batistack (Add-on)

1. Responsabilités du Module

Ce module gère l'intégralité du parc de véhicules et d'engins de chantier du tenant. Il assure le suivi technique (maintenances, contrôles), administratif (assurances, cartes grises) et financier (consommations, loyers), tout en automatisant la refacturation interne des coûts aux chantiers.

2. Entités et Structure de Données

Véhicules & Engins :

immatriculation (ou numéro de parc), marque, modele, type (Utilitaire, Engin, Poids-lourd).

date_mise_en_service, valeur_achat.

unite_usage (Kilomètres ou Heures pour les engins).

status (Disponible, En service, En maintenance, Hors service).

Contrats (Leasing / Assurance) :

prestataire_id (Lien Module Tiers), numero_contrat.

date_echeance, montant_loyer_mensuel.

Assignations (Affectations) :

vehicule_id, salarie_id, chantier_id.

date_debut, date_fin.

Événements & Maintenance :

type (Vidange, Contrôle technique, VGP - Vérification Générale Périodique).

date_prevue, date_realisee, compteur_declenchement.

Coûts de Flotte (Expenses) :

type (Carburant, Péage, Réparation).

montant_ht, volume (litres).

3. Logique d'Imputation Analytique

Le module doit transformer l'usage du matériel en coût de chantier :

Coût de revient : Définition d'un taux interne (ex: 0,50€/km ou 45€/heure).

Calcul automatique : Basé sur les pointages ou les assignations, le système génère un coût "matériel" imputé directement au module Chantiers.

Détection de Conflits : Algorithme vérifiant qu'un véhicule ou un engin n'est pas assigné à deux chantiers ou deux salariés différents sur la même plage horaire.

4. Fonctionnalités Clés

Gestion des Alertes (VGP & Maintenance) : Notifications automatiques basées sur le temps (date) ou l'usage (kilométrage/heures moteur).

Intégration APIs Tiers :

Carburant : Import automatique des flux de cartes carburant (Total, AS24, etc.).

Télématique : Possibilité de connecter des boîtiers GPS pour remonter automatiquement les compteurs.

Planning de Flotte : Vue calendrier des assignations pour optimiser le taux d'utilisation du matériel.

Gestion des Sinistres : Registre des accidents avec photos des constats (via GED) et suivi des remboursements d'assurance.

5. Logique Technique & Packages (Laravel 12.x)

Calculateur de Conflits : Utilisation d'une classe de service dédiée FleetConflictManager utilisant des requêtes de chevauchement de dates (whereBetween).

Schedules : Tâches planifiées journalières pour vérifier les écheances de contrôles techniques et VGP.

Observers : Mise à jour du statut du véhicule (status) dès qu'une assignation est créée ou terminée.

Notifications : Utilisation du système de notification de Laravel (Mail, Database, Slack) pour les alertes critiques de sécurité.

6. Intégrations Core & Add-ons

Chantiers : Imputation des coûts d'usage (km/heures) dans le bilan financier du projet.

RH : Vérification de la validité du permis de conduire ou des CACES avant l'assignation.

Banque : Rapprochement des prélèvements de leasing et des factures de carburant.

Notes de Frais : Liaison des tickets de parking ou de petits entretiens payés par le salarié.

GED : Archivage des cartes grises, contrats d'assurance et rapports de contrôle VGP.

=== .ai/module-ged rules ===

Module GED (Gestion Électronique des Documents) - Batistack (Core)

1. Responsabilités du Module

Ce module sert de coffre-fort numérique centralisé pour le tenant. Il gère le stockage, l'indexation, le versioning et la sécurisation de l'ensemble des documents générés par l'ERP ou téléchargés par les utilisateurs (internes ou tiers).

2. Entités et Structure de Données

Documents (Fichiers) :

uuid (Identifiant unique pour le stockage physique).

nom_original, extension, mime_type, taille.

version (Incrémental).

chemin_s3 (Path isolé par tenant : tenant_{id}/ged/{uuid}).

Métadonnées & Indexation :

titre, description.

tags (JSON ou table pivot pour recherche filtrée).

documentable_type / documentable_id (Lien polymorphique vers Chantiers, Tiers, Salariés, Factures).

Validité & Alertes :

date_expiration (Optionnel).

alerte_active (Boolean).

delai_alerte (Nombre de jours avant expiration).

3. Organisation & Arborescence

Dossiers Virtuels : Système de dossiers géré par base de données pour permettre une organisation flexible sans dépendre de la structure physique du stockage.

Héritage de contexte : Un document lié à un "Chantier A" est automatiquement visible dans la vue documentaire de ce chantier, tout en restant indexé globalement.

4. Fonctionnalités Clés

Versioning (Gestion des versions) : Possibilité de remplacer un plan par une nouvelle version tout en conservant l'historique des modifications.

Alertes d'Expiration : Système critique pour les documents légaux (Attestations de vigilance, Assurances décennales, CACES). Envoi de notifications automatiques au tenant et au tiers concerné.

Visualisation Intégrée : Lecteur de PDF et visualiseur d'images intégré directement dans l'interface sans téléchargement obligatoire.

Signature Électronique (Bridge) : Préparation des documents pour envoi vers des solutions de signature (type DocuSign ou YouSign).

OCR (Reconnaissance de texte) : Extraction automatique de données pour les factures fournisseurs et les notes de frais (Add-on).

5. Logique Technique (Laravel 12.x)

Stockage Cloud : Utilisation de Storage::disk('s3') avec des politiques d'accès privées.

URLs Signées : Pour la sécurité, les documents ne sont jamais publics. Laravel génère des URLs temporaires signées (temporaryUrl) valables quelques minutes pour l'affichage.

Traitement de fichiers : Utilisation de Jobs (ShouldQueue) pour la génération de miniatures (thumbnails) et l'extraction de métadonnées afin de ne pas bloquer l'interface.

Recherche : Utilisation de Laravel Scout pour permettre une recherche rapide dans les titres, descriptions et tags.

6. Intégrations Core & Add-ons

Tiers : Stockage des pièces d'identité et contrats.

Chantiers : Centralisation des plans, PPSPS, et photos de suivi.

Commerce : Archivage automatique des devis et factures validés.

RH : Dossiers confidentiels des salariés (Habilitations, Visites médicales).

Notes de Frais : Archivage des justificatifs numérisés avec valeur probante.

=== .ai/module-gpao rules ===

Module GPAO (Gestion de Production) - Batistack (Add-on)

1. Responsabilités du Module

Ce module gère la transformation des matières premières en produits finis ou semi-finis (ouvrages préfabriqués). Il permet de planifier la production, de suivre l'avancement des ateliers et d'automatiser le réapprovisionnement via un moteur de Calcul des Besoins en Composants (MRP simplifié).

2. Entités et Structure de Données

Ordres de Fabrication (OF) :

reference (OF-YYYY-XXXX), ouvrage_id (Lien vers Module Articles).

quantite_prevue, quantite_produite.

date_debut_prevue, date_fin_prevue.

statut (Brouillon, Planifié, Lancé, En cours, Terminé, Annulé).

chantier_id (Optionnel : si la production est dédiée à un projet spécifique).

Nomenclatures (BOM - Bill of Materials) :

Définition des composants nécessaires pour fabriquer un ouvrage (déjà défini dans le module Articles & Stocks).

Gammes & Opérations :

Liste des étapes de fabrication (ex: Découpe, Assemblage, Peinture, Séchage).

temps_estime par opération.

poste_de_charge_id (Atelier/Machine spécifique).

Besoins en Matériaux (MRP Entries) :

article_id, quantite_requise, date_besoin.

source_type (OF ou Commande Client).

3. Le Moteur MRP (Calcul des Besoins)

Le moteur analyse les stocks et les engagements pour suggérer des actions :

Logique de calcul : Stock Actuel + Commandes Fournisseurs en cours - Besoins des OF lancés - Seuil de sécurité.

Sorties du MRP : * Suggestions d'Achats : Liste d'articles à commander avec quantités et dates critiques.

Alertes de Retard : Notification si un composant manque pour respecter la date de fin d'un OF.

4. Workflow de Production

Création de l'OF : Sélection de l'ouvrage et de la quantité. Le système charge la nomenclature.

Planification : Affectation aux postes de charge et réservation théorique des composants en stock.

Lancement : Validation du début de production.

Consommation & Déstockage : * Déstockage automatique des composants (selon la nomenclature).

Possibilité de déclarer des pertes ou des substitutions de matériaux.

Clôture : Entrée en stock du produit fini et calcul du coût de revient réel (Matières + Temps passé).

5. Fonctionnalités Clés

Planification Visuelle : Interface type Gantt ou Kanban pour visualiser la charge des ateliers.

Suivi de Statut en Temps Réel : Mise à jour via tablette en atelier par les opérateurs.

Génération Automatique d'Achats : Transformation d'une suggestion MRP en Bon de Commande fournisseur en un clic (Module Commerce).

Analyse de Performance : Comparatif Temps Prévu vs Temps Réel pour ajuster les prix de revient des ouvrages.

6. Logique Technique & Packages (Laravel 12.x)

Gestion d'états complexes : Utilisation du package spatie/laravel-model-states pour gérer les transitions de statuts des OF de manière sécurisée (ex: on ne peut pas "Terminer" un OF qui n'a pas été "Lancé").

Calculs Asynchrones : Le moteur MRP est gourmand en ressources. Il sera exécuté via un Job Laravel (ShouldQueue) déclenché à la demande ou chaque nuit.

Traçabilité : Utilisation de spatie/laravel-activitylog pour garder un historique complet des modifications sur chaque OF.

Précision : Utilisation de Decimal pour les quantités et les ratios de conversion d'unités.

7. Intégrations Core & Add-ons

Articles & Stocks : Source des nomenclatures et destination des mouvements de stocks (entrées/sorties).

Commerce : Transformation des suggestions MRP en Bons de Commande.

Chantiers : Lien direct pour les produits fabriqués spécifiquement pour un projet (imputation des coûts).

Pointage : Récupération des temps passés par les ouvriers d'atelier sur les OF.

=== .ai/module-interventions rules ===

Module Interventions - Batistack (Add-on)

1. Responsabilités du Module

Ce module gère les prestations de service ponctuelles, les dépannages et la maintenance. Il permet de planifier les tournées des techniciens, de saisir les rapports d'intervention sur le terrain (via mobile) et de déclencher une facturation immédiate ou différée basée sur la consommation réelle de ressources.

2. Entités et Structure de Données

Bons d'Intervention (BI) :

reference, client_id, tiers_id (si sous-traitance), site_id.

type_intervention (Dépannage, Maintenance préventive, SAV, Installation).

mode_facturation (FORFAIT / RÉGIE).

date_planifiee, heure_debut, heure_fin.

statut (Planifié, En route, En cours, Terminé, Reporté, Facturé).

Lignes d'Intervention :

article_id (Matériel consommé), quantite.

temps_passe (Main-d'œuvre).

is_billable (Boolean).

Rapports d'Intervention :

compte_rendu_technique (Texte).

photos_avant_apres (Lien GED).

signature_client (Image/Blob).

3. Processus Métier (Workflow)

Planification : Création du BI et affectation à un technicien selon ses compétences (Module RH) et sa disponibilité.

Exécution Mobile : Le technicien reçoit sa feuille de route, déclenche un chronomètre à l'arrivée et saisit le matériel utilisé.

Déstockage Intelligent : Le système propose par défaut de déstocker les articles depuis le "Dépôt Mobile" (Camion) assigné au technicien.

Clôture & Signature : Le client signe sur la tablette du technicien. Un rapport PDF est envoyé automatiquement.

Facturation : Transfert automatique vers le module Commerce avec calcul des marges configurables.

4. Fonctionnalités Clés

Gestion de la Régie : Facturation automatique des heures réelles et du petit matériel saisi lors de l'intervention.

Optimisation de Marge : Interface permettant d'ajuster le prix de vente final en fonction du coût de revient réel calculé (Temps + Stock).

Géolocalisation & Itinéraires : Intégration de cartes pour optimiser les trajets des techniciens entre deux interventions.

Historique de Maintenance (Carnet de Santé) : Suivi par site ou par équipement client de toutes les interventions passées.

5. Logique Technique & Packages (Laravel 12.x)

Temps Réel : Utilisation de Laravel Livewire pour la mise à jour des compteurs de temps sur l'interface mobile.

Signature Électronique : Utilisation de bibliothèques JS légères (type signature_pad) pour capturer la signature et la stocker via spatie/laravel-medialibrary.

Actions Dédiées : ConvertInterventionToInvoiceAction pour automatiser la création de la facture en respectant les règles fiscales du tenant.

Gestion des Statuts : Utilisation de spatie/laravel-model-states pour gérer les transitions (ex: on ne peut signer une intervention que si elle est "En cours").

6. Intégrations Core & Add-ons

Articles & Stocks : Déstockage automatique des pièces de rechange depuis le dépôt par défaut du technicien.

Commerce : Génération fluide de la facture finale.

Chantiers : Possibilité d'imputer une intervention complexe à un chantier existant pour une analyse de coût consolidée.

RH : Vérification des habilitations du technicien avant l'affectation d'une intervention à risque (ex: électricité).

GED : Stockage des rapports signés et des photos de preuves.

=== .ai/module-locations rules ===

Module Locations - Batistack (Add-on)

1. Responsabilités du Module

Ce module gère le cycle de vie complet de la location de matériel et d'engins. Il traite deux flux distincts :

Location Fournisseur (Entrante) : Matériel loué par le tenant pour ses propres chantiers.

Location Client (Sortante) : Matériel appartenant au tenant (issu du module Flottes ou Stocks) loué à des tiers.

2. Entités et Structure de Données

Contrats de Location :

reference, type (ENTRANT / SORTANT).

tiers_id (Fournisseur ou Client).

date_debut_prevue, date_fin_prevue.

date_reelle_retour.

statut (Réservé, En cours, Terminé, Annulé, Litige).

Lignes de Location :

article_id ou vehicule_id.

quantite.

tarif_unitaire (par jour, semaine ou mois).

unite_temps (Jour, Heure, Forfait).

États des Lieux (Checklists) :

type (Départ / Retour).

photos_justificatives (Lien GED).

observations_techniques.

compteur_depart, compteur_retour (km ou heures).

3. Gestion Tarification et Disponibilité

Grilles Tarifaires : Gestion de prix dégressifs en fonction de la durée (ex: prix réduit après 5 jours de location).

Calendrier de Disponibilité : Vue graphique permettant de visualiser l'occupation du matériel interne et les dates de restitution attendues pour le matériel loué à l'extérieur.

Prolongations : Workflow de demande de prolongation avec mise à jour automatique des dates de fin et des coûts prévisionnels.

4. Fonctionnalités Clés

Imputation Analytique Automatique : Pour les locations entrantes, le coût est directement imputé au module Chantiers en fonction de la durée réelle.

Refacturation Automatisée : Génération automatique des factures (ou demandes de facturation) pour les locations sortantes basées sur les bons de retour.

Gestion des Sinistres & Assurances : Suivi des dommages constatés lors de l'état des lieux de retour, avec lien vers le module Notes de Frais ou Commerce pour la facturation des réparations.

Alertes de Restitution : Notifications automatiques J-1 avant la date de fin de contrat pour éviter les dépassements non prévus.

5. Logique Technique & Packages (Laravel 12.x)

Calculateur de Coûts Pro-rata : Service RentalCalculator pour gérer les calculs complexes de durées (jours ouvrés vs jours calendaires, prorata d'heures supplémentaires).

Gestion d'États : Utilisation de spatie/laravel-model-states pour sécuriser le flux : un matériel ne peut pas être marqué "Disponible" sans un "État des lieux de retour" validé.

Génération de Documents : Utilisation de barryvdh/laravel-dompdf pour les contrats de location et les procès-verbaux d'état des lieux.

6. Intégrations Core & Add-ons

Chantiers : Imputation des coûts de location (fournisseurs) et suivi du matériel présent sur site.

Flottes : Utilisation des véhicules internes comme ressources pour la location sortante.

Articles & Stocks : Gestion des stocks pour le petit matériel de location (échafaudages, étais).

Commerce : Génération des factures de location (sortant) et intégration des factures fournisseurs (entrant).

GED : Archivage des contrats signés et des photos d'états des lieux.

Banque : Suivi des cautions (dépôts de garantie) et de leurs rendus.

=== .ai/module-note-de-frais rules ===

Module Notes de Frais - Batistack (Add-on)

1. Responsabilités du Module

Ce module permet aux collaborateurs de saisir leurs dépenses professionnelles, de joindre les justificatifs numérisés et de suivre le processus de remboursement. Il assure l'imputation analytique des frais sur les chantiers et la génération automatique des écritures comptables.

2. Entités et Structure de Données

Notes de Frais (En-tête) :

salarie_id, periode (mois/année).

statut (Brouillon, Soumise, En cours de validation, Validée, Remboursée, Rejetée).

total_ht, total_tva, total_ttc.

Lignes de Frais :

date, categorie_id (Repas, Carburant, Péage, Hôtel, Fournitures).

libelle, montant_ht, montant_tva, montant_ttc.

chantier_id (Optionnel pour imputation analytique).

is_billable (Boolean : Indique si le frais doit être refacturé au client).

Catégories de Frais :

nom, code_comptable_id, tva_par_defaut, plafond_max (Alerte si dépassement).

3. Workflow de Capture et Validation

Capture Mobile : Prise en photo du ticket via l'application mobile.

OCR & Extraction : Analyse automatique de l'image pour extraire la date, le montant TTC et la TVA.

Saisie/Correction : Le salarié valide ou corrige les données extraites et affecte le frais à un chantier.

Validation Hiérarchique :

Niveau 1 : Validation par le Conducteur de Travaux (Vérification de l'imputation chantier).

Niveau 2 : Validation par le service Comptabilité (Conformité fiscale et justificatifs).

Mise en Paiement : Marquage comme "Remboursé" après virement.

4. Fonctionnalités Clés

OCR Intégré : Utilisation d'un service d'extraction (type AWS Textract ou Google Vision API) pour limiter la saisie manuelle.

Archivage à Valeur Probante : Liaison avec le module GED pour stocker les justificatifs avec une empreinte numérique (Hachage) garantissant l'intégrité face à l'administration fiscale.

Gestion des Indemnités Kilométriques (IK) : Calcul automatique basé sur la puissance fiscale du véhicule (lié au module Flottes) et la distance parcourue.

Refacturation Client : Identification facilitée des frais à réintégrer dans la prochaine situation de travaux (Module Commerce).

5. Logique Technique & Packages (Laravel 12.x)

Gestion des Médias : Utilisation du package spatie/laravel-medialibrary pour gérer les photos des tickets, leurs conversions et leur stockage sécurisé sur S3.

Processus OCR : Dispatch d'un Job asynchrone (ShouldQueue) dès l'upload du ticket pour ne pas bloquer l'utilisateur pendant l'analyse d'image.

Validation : Utilisation de spatie/laravel-permission pour gérer les droits complexes de validation (Manager de chantier vs Comptable).

Rendu PDF : Utilisation de barryvdh/laravel-dompdf pour générer le récapitulatif mensuel de la note de frais.

6. Intégrations Core & Add-ons

Chantiers : Imputation immédiate des frais validés dans les coûts réels du projet.

Comptabilité : Génération automatique des écritures dans le journal d'Achats (431/6xx/4456) lors de la validation finale.

Banque : Rapprochement entre le remboursement effectué et la note de frais validée.

Flottes : Liaison pour les frais de carburant, péages et entretien liés à un véhicule spécifique.

GED : Archivage sécurisé des justificatifs avec indexation par salarié et par date.

=== .ai/module-paie rules ===

Module Paie (Pré-paie BTP) - Batistack (Add-on)

1. Responsabilités du Module

Ce module n'a pas pour vocation de remplacer un logiciel de paie complet (comme Sage ou SILAE) mais de servir de moteur de pré-paie. Il calcule l'intégralité des variables de rémunération spécifiques au secteur du bâtiment pour permettre une édition ultérieure des bulletins ou un export vers un expert-comptable.

2. Entités et Structure de Données

Périodes de Paie :

mois, annee.

date_ouverture, date_cloture.

statut (Ouverte, En calcul, Validée, Clôturée).

Éléments Variables (Rubriques) :

salarie_id, periode_id.

heures_travaillées_totales, heures_sup_25, heures_sup_50.

absences (CP, RTT, Maladie, Accident du Travail).

indemnites_btp (Paniers, Trajets, Transports, Grands Déplacements).

Grilles de Salaires & Taux :

Configuration des taux de cotisations et des montants d'indemnités selon la zone géographique et la convention collective du tenant.

3. Logique de Calcul Spécifique BTP

Le moteur de calcul doit automatiser les règles complexes du secteur :

Indemnités de Petits Déplacements (IPD) :

Panier Repas : Attribution automatique si le salarié est sur chantier le midi.

Trajet : Indemnisation du temps passé pour se rendre au chantier (Zone 1 à 5).

Transport : Remboursement des frais de déplacement (si véhicule personnel utilisé).

Indemnités de Grands Déplacements (IGD) :

Gestion des forfaits journaliers (logement + nourriture) quand le salarié ne peut rentrer chez lui.

Chômage Intempéries :

Calcul des heures d'arrêt pour cause climatique selon les règles de la CNETP (Caisse Nationale des Entreprises de Travaux Publics).

Primes Spécifiques :

Primes d'insalubrité, de danger, de pénibilité (selon les tâches affectées dans le module Chantiers).

4. Workflow de Pré-Paie

Collecte : Importation automatique des données validées du module Pointage.

Calcul : Lancement du moteur de calcul des variables (Heures + Indemnités).

Révision : Interface de contrôle pour le gestionnaire RH permettant de forcer ou d'ajuster certaines valeurs.

Validation : Verrouillage de la période de paie.

Export : Génération des fichiers de transfert (format JSON/CSV ou connecteurs API) pour le logiciel de paie final.

5. Logique Technique (Laravel 12.x)

Calculateur Haute Précision : Utilisation de classes de services PayrollEngine avec des tests unitaires (Pest/PHPUnit) couvrant tous les cas conventionnels.

Mise en Cache : Utilisation de Redis pour stocker les résultats intermédiaires des calculs sur de gros volumes de salariés.

Évolutivité : Architecture à base de "Drivers" pour supporter différentes conventions collectives (Bâtiment Ouvriers, ETAM, Cadres).

6. Intégrations Core & Add-ons

RH : Récupération du salaire de base, de l'ancienneté et du statut contractuel.

Pointage : Source primaire des heures et des zones de trajet.

Comptabilité : Génération des écritures de provisions pour congés payés et charges sociales.

Notes de Frais : Distinction entre les frais professionnels remboursés au réel et les indemnités forfaitaires de paie.

GED : Archivage des synthèses de paie par salarié.

=== .ai/module-pilotage rules ===

Module Pilotage (Business Intelligence & KPI) - Batistack (Add-on)

1. Responsabilités du Module

Ce module est le "tour de contrôle" de l'entreprise. Sa fonction est de transformer les données brutes issues de la production, de la finance et des RH en indicateurs de performance (KPI) visuels et exploitables pour aider à la prise de décision stratégique et au suivi de la rentabilité.

2. Indicateurs Clés de Performance (KPI)

Le module doit calculer et afficher les indicateurs suivants, segmentés par domaine :

A. Performance des Chantiers

Marge Brute en Temps Réel : (Chiffre d'Affaires Facturé - Coûts Directs Consommés).

Écart Budget/Réel : Différence entre le chiffrage initial et les dépenses réelles (heures, achats, locations).

Reste à Dépenser : Prévisionnel des coûts pour terminer les projets en cours.

B. Santé Financière (Tenant)

Trésorerie Prévisionnelle : Basée sur les factures à échoir, les règlements fournisseurs attendus et les salaires.

Délai Moyen de Paiement (DSO) : Suivi du comportement de paiement des clients.

Rentabilité par Tiers : Identification des clients et fournisseurs les plus rentables.

C. Utilisation des Ressources

Taux d'Utilisation de la Flotte : Pourcentage de temps où les engins et véhicules sont affectés à un chantier.

Productivité RH : Ratio heures produites (chantiers) vs heures payées.

Rotation des Stocks : Fréquence de renouvellement des articles critiques.

3. Structure de Données & Reporting

Tableaux de Bord (Dashboards) :

nom, description, owner_id.

config (JSON : disposition des widgets, filtres par défaut).

Widgets :

type (Graphique linéaire, Histogramme, Jauge, Tableau, KPI simple).

query_logic (Référence à la méthode de calcul ou à l'API interne).

Rapports Programmés :

destinataires (Emails), frequence (Quotidien, Hebdomadaire, Mensuel).

format (PDF, Excel).

4. Système d'Alertes Intelligentes

Le module doit surveiller les dérives et notifier les responsables :

Alerte de Seuil de Marge : Notification si la marge d'un chantier descend sous un seuil critique (ex: < 10%).

Alerte de Trésorerie : Prédiction d'un solde bancaire négatif à 15 jours.

Alerte de Retard : Chantier dont la date de fin réelle dépasse de X% la date prévisionnelle.

5. Logique Technique & Packages (Laravel 12.x)

Agrégation de Données : Utilisation de requêtes SQL optimisées avec Eloquent ou le Query Builder pour calculer les sommes et moyennes sur des millions de lignes (Pointages, Factures).

Visualisation : Intégration de bibliothèques JS de graphiques (type ApexCharts ou Chart.js) via des composants Blade ou Livewire.

Mise en Cache (Redis) : Les KPI ne sont pas recalculés à chaque clic. Utilisation du cache Laravel pour stocker les résultats avec une durée de vie configurable (ex: actualisation toutes les heures).

Tâches Planifiées : Utilisation de Schedule pour générer les rapports lourds durant la nuit.

Package Suggéré : flowframe/laravel-trend pour générer facilement des séries temporelles à partir des modèles Eloquent.

6. Intégrations Multi-modules

Tous les modules : Le module Pilotage est le point final où convergent toutes les données.

Banque : Pour le solde réel et les prévisions de trésorerie.

Chantiers & Commerce : Pour le calcul des marges et l'analyse de rentabilité.

RH & Pointage : Pour l'analyse de productivité et la charge de travail.

=== .ai/module-pointage rules ===

Module Pointage - Batistack (Core)

1. Responsabilités du Module

Ce module permet la capture, la validation et l'imputation du temps de travail des salariés sur les différents chantiers. Il est le moteur de calcul du coût de la main-d'œuvre et fournit les variables nécessaires au calcul de la paie et à l'analyse de rentabilité des projets.

2. Entités et Structure de Données

Pointages (Time Entries) :

salarie_id, chantier_id, phase_id (Lien analytique).

date, heures_normales (nombre), heures_sup (catégorisées : 25%, 50%, etc.).

type_activite (Travail, Intempérie, Trajet, Absence).

commentaire (ex: "Panne machine", "Retard livraison").

Indemnités & Frais (Variables BTP) :

panier_repas (Boolean/Nombre).

zone_trajet (1 à 5 selon la distance du dépôt).

grand_deplacement (Boolean).

Feuilles de Temps (Time Reports) :

Regroupement hebdomadaire par salarié ou par chantier.

statut (Brouillon, Soumis, Validé par Chef de chantier, Validé par RH, Verrouillé).

3. Workflow de Saisie et Validation

Saisie Terrain : Le chef de chantier saisit les heures pour son équipe (saisie collective) ou le salarié saisit ses propres heures via son espace dédié.

Géolocalisation (Optionnelle) : Enregistrement des coordonnées GPS au moment du pointage pour vérifier la présence sur le site.

Validation Hiérarchique : * Niveau 1 : Le Conducteur de Travaux valide l'imputation au chantier.

Niveau 2 : Le service RH valide la conformité pour la paie.

Verrouillage : Une fois la période de paie clôturée, les pointages deviennent immuables.

4. Calcul du Coût Main-d'œuvre

Le système calcule en temps réel l'impact financier sur le chantier :

Formule : (Heures pointées * Coût Horaire Chargé du salarié) + Indemnités (Paniers/Trajets).

Mise à jour : Les coûts sont répercutés instantanément dans le tableau de bord du module Chantiers.

5. Logique Technique (Laravel 12.x)

Calculs complexes : Utilisation de services dédiés (LaborCostCalculator) pour gérer les arrondis et les règles de cumul d'heures.

Interface Réactive : Utilisation de Laravel Livewire pour permettre une saisie rapide "en grille" (matrice salariés / jours) sans rechargement de page.

Optimisation Database : Indexation lourde sur le triplet (salarie_id, chantier_id, date) pour les rapports de synthèse.

Notifications : Envoi d'alertes aux chefs de chantiers le vendredi soir si des pointages sont manquants.

6. Intégrations Core

RH : Récupération du coût_horaire_charge et vérification de la cohérence avec les contrats/absences.

Chantiers : Imputation analytique directe pour le calcul du "Réel" vs "Budget".

Paie : Export des totaux d'heures et des indemnités (paniers, trajets) pour le moteur de pré-paie.

Flottes : Si un salarié pointe un trajet, vérifier si un véhicule lui était assigné.

=== .ai/module-rh rules ===

Module RH (Ressources Humaines) - Batistack (Core)

1. Responsabilités du Module

Ce module centralise la gestion administrative et humaine des collaborateurs du tenant. Dans le secteur du bâtiment, il a une responsabilité critique : assurer la conformité légale (habilitations, visites médicales) et optimiser l'affectation des compétences sur les chantiers.

2. Entités et Structure de Données

Salariés (Profils) :

Extension de l'entité Tiers.

matricule, date_naissance, num_secu, nationalite.

poste_id, equipe_id, site_id.

coût_horaire_charge (Donnée sensible, utilisée pour l'analytique chantier).

Contrats de Travail :

type_contrat (CDI, CDD, Intérim, Apprentissage).

date_debut, date_fin (si applicable).

salaire_base, statut (Ouvrier, ETAM, Cadre).

Certifications & Habilitations (Crucial BTP) :

type (CACES, Habilitation électrique, SST, AIPR).

date_obtention, date_expiration.

document_proof (Lien vers la GED).

Visites Médicales :

date_derniere_visite, date_echeance, aptitude (Apte, Apte avec réserves, Inapte).

3. Gestion des Compétences et Certifications

Le module doit agir comme un système de surveillance active :

Skills Mapping : Matrice des compétences permettant de trouver rapidement un profil pour un besoin spécifique (ex: "Chercher un soudeur avec habilitation à jour").

Système d'Alertes : Notification automatique (Email/In-app) 30, 60 et 90 jours avant l'expiration d'une certification ou d'une visite médicale.

Tableau de Bord de Conformité : Vue globale du taux de conformité des équipes avant l'envoi sur un chantier sensible.

4. Fonctionnalités Clés

Dossier Numérique Unique : Centralisation de tous les documents (CI, Permis, Contrat, Mutuelle) avec chiffrement des données sensibles.

Gestion des Absences : Workflow de demande de congés (CP, RTT, CSS) et saisie des arrêts de travail / accidents de travail (AT).

Organigramme Dynamique : Visualisation de la structure hiérarchique de l'entreprise.

Suivi des Équipements (EPI) : Registre de dotation des Équipements de Protection Individuelle (Chaussures, Casque, Harnais) avec dates de renouvellement.

5. Logique Technique (Laravel 12.x)

Sécurité & Chiffrement : Utilisation de Casts Eloquent pour chiffrer les données sensibles en base (ex: num_secu via EncryptedCast).

Traitement Planifié (Schedule) : Commande journalière rh:check-expirations pour scanner les dates et déclencher les notifications.

Gestion des Fichiers : Utilisation de disques S3 privés avec des URLs temporaires signées pour la consultation des documents RH.

6. Intégrations Core & Add-ons

Tiers : Le salarié possède un compte "User" lié à son profil Tiers pour accéder à son espace Self-Service.

Pointage : Source de données pour vérifier la présence réelle vs les absences saisies.

Paie : Transmission des éléments fixes et variables (salaire, primes, absences) pour le calcul du bulletin.

Flottes : Vérification de la validité du permis de conduire avant l'assignation d'un véhicule.

GED : Stockage sécurisé et versionné de tous les documents contractuels.

=== .ai/module-tiers rules ===

Module Tiers - Batistack (Core)

1. Responsabilités du Module

Ce module est le pivot relationnel de l'ERP. Il centralise toutes les entités externes et internes interagissant avec le tenant. Sa mission est d'assurer l'identification, la classification et l'accès sécurisé de chaque acteur à son espace dédié.

2. Typologie des Tiers

Chaque tiers est classé selon un type principal, mais le système doit permettre la multi-classification (ex: un tiers peut être à la fois Fournisseur et Sous-traitant).

Clients : Maîtres d'ouvrage ou entreprises donneuses d'ordre.

Fournisseurs : Entités vendant des matériaux ou des services (hors sous-traitance).

Sous-traitants : Entreprises intervenant sur chantier avec un contrat de sous-traitance.

Salariés : Personnel interne à l'entreprise du tenant.

3. Entités et Modèle de Données (Tenant Side)

Profil Tiers (Générique) : * Raison sociale / Nom / Prénom.

Identifiant unique (Code Tiers automatisé).

Coordonnées (Adresse, Tel, Email, Site web).

Informations légales (SIRET, N° TVA, Code NAF).

Données financières (RIB, Conditions de règlement par défaut).

Utilisateurs (Auth) : * Chaque tiers peut posséder un ou plusieurs comptes "Utilisateur".

email, password, is_active.

Lien vers le tiers parent via une table pivot pour gérer les accès multi-tiers si nécessaire.

4. Architecture des Espaces Dédiés (Portails)

L'accès est segmenté via des "Guards" Laravel ou des scopes de session pour garantir que chaque tiers ne voit que ses propres données.

A. Espace Client

Consultation des devis en attente (avec validation électronique).

Suivi de l'avancement des chantiers (photos, documents partagés).

Accès aux factures et situations de travaux.

Paiement en ligne (si module Banque/Paiement activé).

B. Espace Fournisseur & Sous-traitant

Réception et réponse aux demandes de prix (Appels d'offres).

Dépôt des documents administratifs obligatoires (Attestations vigilance, assurances).

Saisie des situations de travaux (pour les sous-traitants).

Accès aux bons de commande.

C. Espace Salarié (Self-Service)

Consultation des bulletins de paie (flux provenant du module Paie).

Saisie des feuilles d'heures (lien direct vers le module Pointage).

Demandes de congés et dépôt de notes de frais (lien vers module RH/Frais).

Accès au planning de chantier.

5. Sécurité et Permissions

Rôles granulaires : Utilisation de permissions par tiers (ex: un "Conducteur de travaux" chez un sous-traitant n'a pas les mêmes droits qu'un "Comptable").

Gestion des invitations : Workflow d'invitation par email pour que le tiers crée son mot de passe et active son espace.

Audit : Journalisation des connexions et des téléchargements de documents sensibles.

6. Intégrations Core

GED : Stockage automatique des documents légaux (KBIS, Assurances) avec système d'alerte de péremption.

Commerce : Récupération automatique des informations de facturation.

Chantiers : Affectation des tiers (Salariés/Sous-traitants) aux projets.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.16
- filament/filament (FILAMENT) - v5
- laravel/cashier (CASHIER) - v16
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== herd rules ===

## Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate URLs for the user to ensure valid URLs.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version-specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== fluxui-free/core rules ===

## Flux UI Free

- This project is using the free edition of Flux UI. It has full access to the free components and variants, but does not have access to the Pro components.
- Flux UI is a component library for Livewire. Flux is a robust, hand-crafted UI component library for your Livewire applications. It's built using Tailwind CSS and provides a set of components that are easy to use and customize.
- You should use Flux UI components when available.
- Fallback to standard Blade components if Flux is unavailable.
- If available, use the `search-docs` tool to get the exact documentation and code snippets available for this project.
- Flux UI components look like this:

<code-snippet name="Flux UI Component Example" lang="blade">
    <flux:button variant="primary"/>
</code-snippet>

### Available Components
This is correct as of Boost installation, but there may be additional components within the codebase.

<available-flux-components>
avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, otp-input, profile, radio, select, separator, skeleton, switch, text, textarea, tooltip
</available-flux-components>

=== livewire/core rules ===

## Livewire

- Use the `search-docs` tool to find exact version-specific documentation for how to write Livewire and Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle Hook Examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire Component Test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

<code-snippet name="Testing Livewire Component Exists on Page" lang="php">
    $this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== pest/v4 rules ===

## Pest 4

- Pest 4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest 4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind CSS

- Use Tailwind CSS classes to style HTML; check and use existing Tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc.).
- Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, and group elements logically.
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing; don't use margins.

<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>

### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind CSS 4

- Always use Tailwind CSS v4; do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.

<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>

### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option; use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow existing conventions for how and where it's implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices.

### Artisan

- Use Filament-specific Artisan commands to create files. Find them with `list-artisan-commands` or `php artisan --help`.
- Inspect required options and always pass `--no-interaction`.

### Patterns

Use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),
</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),
</code-snippet>

Actions encapsulate a button with optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->form([
        TextInput::make('email')->email()->required(),
    ])
    ->action(fn (array $data, User $record): void => $record->update($data)),
</code-snippet>

### Testing

Authenticate before testing panel functionality. Filament uses Livewire, so use `livewire()` or `Livewire::test()`:

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Test',
            'email' => 'test@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Test',
        'email' => 'test@example.com',
    ]);
</code-snippet>

<code-snippet name="Testing Validation" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => null,
            'email' => 'invalid-email',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'email',
        ])
        ->assertNotNotified();
</code-snippet>

<code-snippet name="Calling Actions" lang="php">
    use Filament\Actions\DeleteAction;
    use Filament\Actions\Testing\TestAction;

    livewire(EditUser::class, ['record' => $user->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    livewire(ListUsers::class)
        ->callAction(TestAction::make('promote')->table($user), [
            'role' => 'admin',
        ])
        ->assertNotified();
</code-snippet>

### Common Mistakes

**Commonly Incorrect Namespaces:**
- Form fields (TextInput, Select, etc.): `Filament\Forms\Components\`
- Infolist entries (for read-only views) (TextEntry, IconEntry, etc.): `Filament\Infolists\Components\`
- Layout components (Grid, Section, Fieldset, Tabs, Wizard, etc.): `Filament\Schemas\Components\`
- Schema utilities (Get, Set, etc.): `Filament\Schemas\Components\Utilities\`
- Actions: `Filament\Actions\` (no `Filament\Tables\Actions\` etc.)
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

**Recent breaking changes to Filament:**
- File visibility is `private` by default. Use `->visibility('public')` for public access.
- `Grid`, `Section`, and `Fieldset` no longer span all columns by default.
</laravel-boost-guidelines>

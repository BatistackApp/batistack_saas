### Liste de Recommandations Professionnelles (Module GED)

#### 1. Affiner la Typologie des Documents (`DocumentType.php`)

**Observation :**
L'énumération `DocumentType` est un bon début, mais elle reste assez générique. Le cas `Certificate` par exemple, regroupe des documents de nature et de criticité très différentes dans le BTP.

**Recommandation BTP :**
Dans notre secteur, la distinction entre les différents types d'attestations est cruciale, notamment pour la gestion des sous-traitants. Un manquement sur une attestation de vigilance URSSAF ou une assurance décennale peut avoir des conséquences juridiques et financières lourdes.

**Suggestion Technique :**
Enrichir l'`Enum` pour refléter cette réalité métier. Cela permettra plus tard de déclencher des logiques de validation ou d'alertes spécifiques.

```php
// app/Enums/GED/DocumentType.php (suggestion d'évolution)

enum DocumentType: string implements HasLabel
{
    //...
    case Plan = 'plan'; // Plan d'exécution, de coffrage, etc.
    case TechnicalDoc = 'technical_doc'; // Fiche technique produit, Avis Technique (ATec)

    // Catégorie "Administratif & Légal" - CRITIQUE
    case DecennialInsurance = 'decennial_insurance'; // Assurance Décennale
    case UrssafVigilance = 'urssaf_vigilance';     // Attestation de vigilance URSSAF
    case Kbis = 'kbis';                          // Extrait Kbis
    case ProfessionalLicense = 'professional_license'; // Habilitations (AIPR, CACES, Électrique)

    case Ppsps = 'ppsps'; // Plan Particulier de Sécurité et de Protection de la Santé
    case Doe = 'doe';     // Dossier des Ouvrages Exécutés

    case Photo = 'photo'; // Photo de chantier, de malfaçon, d'avancement
    case Identity = 'identity';
    case Contract = 'contract'; // Contrat de sous-traitance, de travail
    case Invoice = 'invoice';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            // ... ajouter les nouvelles traductions dans lang/fr/ged.php
            self::DecennialInsurance => __('ged.document_types.decennial_insurance'),
            self::UrssafVigilance => __('ged.document_types.urssaf_vigilance'),
            self::Ppsps => __('ged.document_types.ppsps'),
            // ...
        };
    }
}
```

#### 2. Renforcer la Gestion de la Validité et des Alertes (`Document.php`)

**Observation :**
Le modèle `Document` contient un champ `expires_at`. C'est excellent. Cependant, la logique d'alerte n'est pas encore matérialisée dans le modèle.

**Recommandation BTP :**
La principale valeur ajoutée d'une GED dans le BTP est la surveillance pro-active de la péremption des documents administratifs. Un conducteur de travaux doit être notifié **avant** l'échéance, et non le jour J. Le délai de prévenance peut varier (ex: 90 jours pour une assurance, 30 jours pour une habilitation).

**Suggestion Technique :**
Ajouter des champs au modèle `Document` et à la migration pour piloter finement les alertes.

```php
// database/migrations/2026_02_05_164108_create_documents_table.php (suggestion d'ajout)
$table->date('expires_at')->nullable();
$table->integer('alert_before_days')->nullable(); // Ex: 30, 60, 90 jours
$table->timestamp('last_alert_sent_at')->nullable(); // Pour éviter le spam
$table->boolean('is_mandatory')->default(false); // Pour identifier les docs bloquants

// app/Models/GED/Document.php (suggestion d'ajout)
protected function casts(): array
{
    return [
        'metadata' => 'array',
        'expires_at' => 'date',
        'last_alert_sent_at' => 'datetime',
        // ...
    ];
}
```
Cela permettra à une tâche planifiée (`Scheduled Task`) de scanner chaque jour les documents arrivant à échéance et de déclencher des notifications ciblées.

#### 3. Introduire la Notion de Versionning, Surtout pour les Plans (`Document.php`)

**Observation :**
Le champ `version` est présent et initialisé à `1`. C'est une très bonne base.

**Recommandation BTP :**
Sur un chantier, travailler avec un plan qui n'est pas à jour (obsolète) est une source d'erreurs coûteuses (malfaçons, retards). La gestion des indices de plans (Indice A, B, C...) est fondamentale. Il faut pouvoir "remplacer" un plan par une nouvelle version tout en gardant l'historique, et surtout, marquer l'ancien comme "Archivé" ou "Obsolète".

**Suggestion Technique :**
Pour gérer cela proprement, on pourrait introduire une relation sur le modèle lui-même.

```php
// database/migrations/2026_02_05_164108_create_documents_table.php (suggestion d'ajout)
$table->foreignId('replaces_document_id')->nullable()->constrained('documents')->nullOnDelete();
$table->string('status')->default('active'); // ex: 'active', 'archived', 'obsolete'

// app/Models/GED/Document.php (suggestion de relations)
public function previousVersions(): BelongsTo
{
    return $this->belongsTo(Document::class, 'replaces_document_id');
}

public function nextVersion(): HasOne
{
    return $this->hasOne(Document::class, 'replaces_document_id');
}
```
Ainsi, lors de l'upload d'une nouvelle version d'un plan, on créerait un nouveau document en renseignant `replaces_document_id` et on mettrait à jour le statut de l'ancien.

#### 4. Améliorer les Métadonnées Contextuelles (`StoreDocumentRequest.php`)

**Observation :**
La requête permet d'associer des `tags` et une `description`. C'est flexible, mais pas assez structuré pour le BTP.

**Recommandation BTP :**
Les documents de chantier sont souvent liés à des contextes précis. Une photo n'a de valeur que si l'on sait de quelle **zone** du chantier elle parle. Un plan est lié à un **lot** ou une **phase** spécifique (Gros-Œuvre, CVC, Plomberie...).

**Suggestion Technique :**
Utiliser le champ `metadata` (JSON) pour stocker des informations structurées, potentiellement en fonction du type de document. L'interface utilisateur pourrait afficher des champs spécifiques si `document_type` est `Plan` ou `Photo`.

```php
// Exemple de données dans le champ `metadata` du modèle Document

// Pour un document de type 'Plan'
'metadata' => [
    'lot' => 'CVC - Plomberie',
    'phase' => 'Exécution',
    'indice' => 'B'
]

// Pour un document de type 'Photo'
'metadata' => [
    'zone' => 'Bâtiment A - R+2 - Appartement 203',
    'subject' => 'Mise en place des réservations'
]
```
Cela rendra la recherche et le filtrage beaucoup plus puissants que de simples tags.

#### 5. Optimisation des Miniatures (`GenerateThumbnailJob.php`)

**Observation :**
La génération de miniatures pour PDF et images est une excellente fonctionnalité pour l'expérience utilisateur.

**Recommandation BTP :**
Les fichiers de notre secteur peuvent être très variés. On pense notamment aux fichiers `.dwg` (AutoCAD) ou `.ifc` (BIM). Bien que leur traitement direct sur le serveur soit complexe et hors de portée, l'interface ne doit pas "casser". De plus, certains documents comme un CCTP (Cahier des Clauses Techniques Particulières) peuvent faire des centaines de pages.

**Suggestion Technique :**
1.  **Gérer les cas non supportés :** Dans le `Job`, si l'extension n'est ni un PDF ni une image, enregistrer une icône par défaut dans `metadata['thumbnail']` (ex: `icons/dwg.svg`, `icons/ifc.svg`). L'UI sera plus propre.
2.  **Optimisation PDF :** La librairie `spatie/pdf-to-image` permet de spécifier la page à convertir. Pour les documents volumineux, se limiter à la première page est une bonne pratique déjà en place, qu'il faut conserver.
3.  **Optimisation d'Image :** Le `composer.json` ajoute `spatie/laravel-image-optimizer`. Il serait judicieux de l'utiliser après la création de la miniature pour réduire son poids, surtout pour les accès mobiles sur chantier.

```php
// app/Jobs/GED/GenerateThumbnailJob.php (suggestion d'évolution)
use Spatie\ImageOptimizer\OptimizerChainFactory;

// ... dans handle()
if ($this->document->extension === 'pdf') {
    // ...
} elseif (in_array($this->document->extension, ['jpg', 'jpeg', 'png', 'webp'])) {
    // ...
    // Ajouter l'optimisation
    OptimizerChainFactory::create()->optimize($destPath);
} else {
    // Cas non géré : on pourrait stocker un chemin vers une icône générique
    $iconPath = 'icons/' . $this->document->extension . '.svg';
    $this->document->update([
        'metadata' => array_merge($this->document->metadata ?? [], ['thumbnail' => $iconPath])
    ]);
    return; // Stop execution
}

// Mettre à jour les métadonnées
$this->document->update([...]);
```

### Synthèse

La base technique est excellente. En injectant ces logiques métier BTP, le module GED passera du statut d'un simple "stockage de fichiers" à celui d'un **outil pro-actif de gestion des risques et de conformité administrative**, ce qui est une proposition de valeur immense pour une entreprise du bâtiment.

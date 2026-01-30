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
- Etape 6: Génération des FormRequest associé
- Etape 7: Génération des REST-API Controller permettant et préparant les intéractions basiques du module.
- Etape 8: Etablissement des Tests Unitaires/Features en corrélation avec le Module dans le format PESTPHP.

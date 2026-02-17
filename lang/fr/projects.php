<?php

return [
    'status' => [
        'study' => "En phase d'étude / Devis",
        'accepted' => 'Accepté',
        'in_progress' => 'Chantier ouvert',
        'suspended' => "Chantier à l'arrêt",
        'finished' => 'Réceptionné',
        'archived' => 'Clôturé administrativement',
        'cancelled' => 'Annulé',
    ],
    'user_role' => [
        'project_manager' => 'Conducteur de travaux',
        'site_manager' => 'Chef de chantier',
        'contractor' => 'Contratant',
        'other' => 'Autre',
    ],
    'phases' => [
        'status' => [
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'on_hold' => 'En pause / Aléa technique',
            'finished' => 'Terminé / Réceptionné',
        ],
    ],
    'suspension_reasons' => [
        'weather' => 'Météo',
        'client_decision' => 'Décision du client',
        'supply_issue' => 'Problème de fourniture',
        'technical_issue' => 'Problème technique',
        'administrative' => 'Administratif',
    ],
    'amendments_status' => [
        'pending' => 'En attente',
        'accepted' => 'Accepté',
        'refused' => 'Refusé',
    ],
];

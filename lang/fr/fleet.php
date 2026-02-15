<?php

return [
    'vehicle_types' => [
        'van' => 'Fourgon',
        'truck' => 'Camion',
        'excavator' => 'Excavatrice',
        'loader' => 'Chargeuse',
        'crane' => 'Grue',
        'car' => 'Voiture',
        'other' => 'Autre',
    ],
    'fuel_types' => [
        'diesel' => 'Diesel',
        'gnr' => 'GNR',
        'electric' => 'Électrique',
        'petrol' => 'Essence',
        'hybrid' => 'Hybride',
    ],
    'maintenance_statuses' => [
        'scheduled' => 'Planifié',
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
        'reported' => 'Reporté',
    ],
    'inspection_types' => [
        'ct' => 'Contrôle Technique',
        'vgp' => 'Vérification Générale Périodique (VGP)',
        'tachograph' => 'Chronotachygraphe',
    ],
    'fines_statuses' => [
        'received' => 'Reçu',
        'driver_assigned' => 'Conducteur assigné',
        'contested' => 'Contesté',
        'paid' => 'Payé',
        'archived' => 'Archivé',
    ],
    'designation_statuses' => [
        'none' => 'Aucune désignation nécessaire',
        'pending' => 'En attente d\'envoi à l\'ANTAI',
        'sent' => 'Envoyé',
        'confirmed' => 'Confirmé par l\'ANTAI',
        'exported' => 'Exporté (fichier généré mais pas encore envoyé)',
    ],
    'maintenance_types' => [
        'preventative' => 'Entretien préventif',
        'curative' => 'Entretien curatif',
        'regulatory' => 'Contrôles réglementaires',
        'preventative_description' => 'Entretien planifié pour prévenir les pannes et prolonger la durée de vie du véhicule.',
        'curative_description' => 'Entretien nécessaire en cas de panne ou d\'incident pour remettre le véhicule en état de fonctionnement.',
        'regulatory_description' => 'Contrôles obligatoires pour assurer la conformité réglementaire, tels que les contrôles techniques ou les vérifications périodiques.',
    ],
];

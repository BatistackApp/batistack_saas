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
    ],
];

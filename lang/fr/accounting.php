<?php

return [
    'journal_types' => [
        'VT' => 'Ventes',
        'AC' => 'Achats',
        'BQ' => 'Banque',
        'DV' => 'Divers',
        'NDF' => 'Notes de frais',
        'LOC' => 'Locations',
    ],
    'account_types' => [
        'asset' => 'Actif',
        'liability' => 'Passif',
        'equity' => 'Capitaux propres',
        'income' => 'Produits',
        'expense' => 'Charges',
    ],
    'entry_statuses' => [
        'draft' => 'Brouillon',
        'posted' => 'Comptabilisée',
        'locked' => 'Verrouillée',
    ],
];

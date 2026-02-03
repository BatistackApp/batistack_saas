<?php

return [
    'purchase_order' => [
        'statuses' => [
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            'partially_received' => 'Partiellement reçu',
            'received' => 'Reçu',
            'invoiced' => 'Facturé',
            'cancelled' => 'Annulé',
        ],
    ],
    'quote' => [
        'statuses' => [
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            'accepted' => 'Accepté',
            'rejected' => 'Rejeté',
            'lost' => 'Perdu',
        ],
    ],
    'invoice' => [
        'types' => [
            'deposit' => 'Facture d\'acompte',
            'progress' => 'Situation de travaux (BTP)',
            'final' => 'Facture de solde finale',
            'credit_note' => 'Avoir',
            'normal' => 'Facture normale',
        ],
        'statuses' => [
            'draft' => 'Brouillon',
            'validated' => 'Validé',
            'partially_paid' => 'Partiellement payé',
            'paid' => 'Payé',
            'overdue' => 'Impayé',
        ],
    ],
];

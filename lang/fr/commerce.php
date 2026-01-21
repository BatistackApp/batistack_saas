<?php

return [
    'status' => [
        'draft' => 'Brouillon',
        'validated' => 'Validé',
        'accepted' => 'Accepté',
        'invoiced' => 'Facturé',
        'partially_paid' => 'Partiellement Payé',
        'paid' => 'Payé',
        'cancelled' => 'Annulé',
    ],
    'document_type' => [
        'quote' => 'Devis',
        'purchase_order' => 'Bon de commande',
        'delivery' => 'Bon de livraison',
        'invoice_progress' => 'Facture d\'avancement',
        'invoice_final' => 'Facture finale',
        'amendment' => 'Avenant',
        'credit_note' => 'Note de crédit',
        'payment' => 'Paiement',
    ],
    'commande_status' => [
        'draft' => 'Brouillon',
        'confirmed' => 'Confirmé',
        'partially_delivered' => 'Partiellement Livré',
        'delivered' => 'Livré',
        'cancelled' => 'Annulé',
    ],
    'facture_type' => [
        'standard' => 'Standard',
        'progress' => 'Avancement',
        'final' => 'Finale',
    ],
    'type_paiement' => [
        'espece' => 'Espèce',
        'cheque' => 'Chèque',
        'virement' => 'Virement',
        'carte_bancaire' => 'Carte Bancaire',
        'paypal' => 'PayPal',
        'autre' => 'Autre',
    ],
];

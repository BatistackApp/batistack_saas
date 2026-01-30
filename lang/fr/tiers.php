<?php

return [
    'tier_type' => [
        'customer' => 'Client',
        'supplier' => 'Fournisseur',
        'subcontractor' => 'Sous-traitant',
        'employee' => 'Employé',
    ],
    'tier_status' => [
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'suspended' => 'Suspendu',
        'archived' => 'Archivé',
    ],
    'notifications' => [
        'welcome_subject' => 'Bienvenue chez nous !',
        'welcome_greeting' => 'Bonjour :name,',
        'welcome_message' => 'Nous sommes ravis de vous compter parmi nos :type. N\'hésitez pas à nous contacter si vous avez des questions.',
        'document_expiration_subject' => 'Notification d\'expiration de document',
        'document_expiration_message' => 'Le document de :name est sur le point d\'expirer. Veuillez le renouveler dès que possible.',
    ],
    'tier_document_status' => [
        'valid' => 'Valide',
        'to_renew' => 'À renouveler',
        'expired' => 'Expiré',
        'missing' => 'Manquant',
    ],
    'tier_payment_term' => [
        'at_receipt' => 'À la réception',
        'net_15' => 'Net 15 jours',
        'net_30' => 'Net 30 jours',
        'net_45' => 'Net 45 jours',
        'net_60' => 'Net 60 jours',
        'end_of_month_30' => 'Fin de mois + 30 jours',
        'end_of_month_60' => 'Fin de mois + 60 jours',
    ],
];

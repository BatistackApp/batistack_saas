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
];

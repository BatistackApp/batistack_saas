<?php
return [
    'leave_status' => [
        'pending' => 'En attente',
        'approved' => 'Validé',
        'rejected' => 'Rejeté'
    ],
    'leave_type' => [
        'paid_leave' => 'Congé payé',
        'unpaid_leave' => 'Congé sans solde',
        'sick_leave' => 'Maladie',
        'other' => 'Autre'
    ],
    'timesheet_status' => [
        'draft' => 'Brouillon',
        'submitted' => 'Soumis',
        'validated' => 'Validé'
    ],
    'notifications' => [
        'timesheet_submitted_subject' => 'Votre tableau horaire à été validé',
        'hello' => 'Bonjour $name',
        'timesheet_submitted_body' => 'Votre tableau horaire pour le $date a été validé.',
        'view_timesheet' => 'Voir le tableau horaire',

        'leave_approved_subject' => 'Votre congé a été validé',
        'leave_approved_body' => 'Votre congé de $start_date à $end_date a été validé.',

        'leave_rejected_subject' => 'Votre congé a été rejeté',
        'leave_rejected_body' => 'Votre congé de $start_date à $end_date a été rejeté.',
        'rejection_reason' => 'Raison du rejet : $reason'
    ],
];

@extends("emails.layout")

@section('content')
    <div class="subtitle">Numéro de transmission : {{ $report_number }}</div>

    <div class="title">
        Transmission de l'export comptable pour la période de {{ $periodName }}.
    </div>

    <div class="content">
        Madame, Monsieur,<br><br>
        Nous vous informons que la clôture de la paie pour la période du **{{ $startDate }}** au **{{ $endDate }}** a été effectuée avec succès.<br>
        Vous trouverez en pièce jointe le fichier des écritures comptables (OD de paie) formaté pour votre logiciel de comptabilité.
    </div>

    <!-- Section Tableau Récapitulatif -->
    <div class="content">
        <table class="table">
            <thead>
            <tr>
                <th>Indicateur</th>
                <th>Donnée</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>Nombre de bulletins</strong></td>
                <td>{{ $count }} salariés</td>
            </tr>
            <tr>
                <td><strong>Masse nette à payer</strong></td>
                <td>{{ number_format($totalNet, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td><strong>Statut de la période</strong></td>
                <td>Validée / Prête pour virement</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="content">
        Le fichier joint contient le détail des comptes 641, 421 et les cotisations sociales associées. Pour toute question relative à ces données, vous pouvez consulter le détail sur votre interface Batistack.
    </div>

    <!-- Bloc d'action style "France Travail" / Batistack -->
    <div class="button-container">
        <div class="button-icon">
            <svg width="24" height="24" fill="#002157" viewBox="0 0 16 16">
                <path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3zm2 2v2h2V5H3zm0 3v2h2V8H3zm4-3v2h6V5H7zm0 3v2h6V8H7z"/>
            </svg>
        </div>
        <div>
            <strong>Consulter la période sur l'ERP</strong><br>
            <span style="font-size: 13px; color: #666;">Accédez directement au détail des lignes de paie.</span><br>
            <a href="{{ url('/admin/payroll/periods/' . $period->id) }}" style="color: #002157; font-weight: bold; text-decoration: none;">
                Ouvrir Batistack →
            </a>
        </div>
    </div>

    <div class="content" style="font-size: 12px; color: #777; font-style: italic;">
        Note : Ce fichier est généré automatiquement par le module Paie & Analytique.
    </div>
@endsection

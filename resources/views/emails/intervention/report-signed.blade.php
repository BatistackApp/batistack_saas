@extends("emails.layout")

@section('content')
    <div class="subtitle">Référence intervention : {{ $intervention->reference }}</div>

    <div class="title">
        Votre rapport d'intervention est disponible.
    </div>

    <div class="content">
        Bonjour {{ $customerName }},<br><br>
        Nous vous informons que l'intervention concernant "<strong>{{ $label }}</strong>" a été clôturée avec succès le {{ $date }}.<br>
        Vous trouverez ci-joint le bon d'intervention (Bon d'Attachement) validé par nos équipes.
    </div>

    <!-- Résumé de l'intervention -->
    <div class="content">
        <table class="table">
            <thead>
            <tr>
                <th>Détail</th>
                <th>Informations</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>Référence</strong></td>
                <td>{{ $intervention->reference }}</td>
            </tr>
            <tr>
                <td><strong>Description</strong></td>
                <td>{{ $intervention->description ?? 'Non renseignée' }}</td>
            </tr>
            @if($intervention->technicians->isNotEmpty())
                <tr>
                    <td><strong>Technicien(s)</strong></td>
                    <td>
                        {{ $intervention->technicians->pluck('name')->join(', ') }}
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <div class="content">
        Ce document atteste de la réalisation des prestations et des fournitures utilisées. Nous restons à votre disposition pour toute information complémentaire.
    </div>

    <!-- Bloc d'action interactif -->
    <div class="button-container">
        <div class="button-icon">
            <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                <path d="M4.5 12.5A.5.5 0 0 1 5 12h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 10h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5z"/>
            </svg>
        </div>
        <div>
            <strong>Espace Client Batistack</strong><br>
            <span style="font-size: 13px; color: #666666;">
                Consultez l'historique de vos interventions et vos factures en ligne.
            </span><br>
            <a href="{{ url('/customer/portal') }}" style="display: inline-block; margin-top: 5px; font-weight: bold;">Accéder à mon compte →</a>
        </div>
    </div>
@endsection

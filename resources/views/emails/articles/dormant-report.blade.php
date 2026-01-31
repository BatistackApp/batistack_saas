@extends("emails.layout")
@section('content')
    <div class="subtitle">Numéro de rapport : {{ $report_number }}</div>

    <div class="title">
        Accusé de réception de votre rapport de stock dormant du mois de {{ $month_year }}.
    </div>

    <div class="content">
        Bonjour M. {{ $recipient_name }},<br><br>
        Votre rapport d'audit pour le mois de {{ $month_year }} a bien été généré le {{ $date_full }} depuis le serveur Batistack.<br>
        Il identifie les articles n'ayant eu aucun mouvement au cours des 6 derniers mois pour le compte de **{{ $tenant_name }}**.
    </div>

    <!-- Section Tableau des Articles -->
    <div class="content">
        <table class="table">
            <thead>
            <tr>
                <th>Référence</th>
                <th>Article</th>
                <th>Stock Actuel</th>
            </tr>
            </thead>
            <tbody>
            @foreach($articles as $article)
                <tr>
                    <td><strong>{{ $article->sku }}</strong></td>
                    <td>{{ $article->name }}</td>
                    <td>{{ number_format($article->total_stock, 2) }} {{ $article->unit }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="content">
        Vous pouvez retrouver le détail complet de vos stocks et gérer vos approvisionnements à tout moment dans <a href="{{ url('/admin/inventory') }}">votre espace professionnel</a>.
    </div>

    <!-- Bloc d'action style "France Travail" -->
    <div class="button-container">
        <div class="button-icon">
            <!-- Icone SVG simple pour simuler l'application -->
            <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
            </svg>
        </div>
        <div>
            <strong>Accéder à Batistack Mobile</strong><br>
            <span style="font-size: 13px; color: #666666;">Consultez vos stocks sur le chantier</span>
        </div>
    </div>

    <div class="content">
        Cordialement,<br>
        Votre service logistique Batistack
    </div>
@endsection

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis - {{ $quote->reference }}</title>
    <!-- Utilisation de Tailwind pour le style du PDF -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'Noto Sans';
            src: url('https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap');
        }
        body {
            font-family: 'Noto Sans', sans-serif;
            font-size: 12px;
            color: #1a202c;
        }
        /* Style spécifique pour les sauts de page mentionné dans le Canvas */
        .page-break {
            page-break-after: always;
        }
        .text-blue-batistack {
            color: #002157;
        }
        .bg-blue-batistack {
            background-color: #002157;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #002157;
            color: white;
            padding: 8px;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .bg-gray-header {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-white p-8">
<!-- En-tête -->
<div class="flex justify-between items-start mb-12">
    <div>
        <!-- [Image du Logo Batistack] -->
        <div class="text-3xl font-bold text-blue-batistack mb-2">BATISTACK</div>
        <div class="text-gray-600">
            <p>{{ $tenant->name }}</p>
            <p>123 Avenue du Chantier</p>
            <p>75001 Paris</p>
            <p>SIRET : 123 456 789 00012</p>
        </div>
    </div>
    <div class="text-right">
        <h1 class="text-2xl font-bold uppercase mb-4">Devis</h1>
        <p class="text-lg font-semibold">{{ $quote->reference }}</p>
        <p class="text-gray-600">Date : {{ $quote->created_at->format('d/m/Y') }}</p>
        <p class="text-gray-600">Validité : {{ $quote->valid_until->format('d/m/Y') }}</p>
    </div>
</div>

<!-- Informations Client et Projet -->
@yield('content')

<!-- Pied de page -->
<div class="mt-20 pt-8 border-t text-center text-gray-500 text-xs">
    <p class="mb-2">Bon pour accord le : ___/___/20___</p>
    <p class="mb-8">Signature du client (précédée de la mention "Lu et approuvé") :</p>

    <div class="grid grid-cols-3 gap-4 text-[10px]">
        <p>APE : 4322A</p>
        <p>Capital Social : 50 000 €</p>
        <p>Assurance RC : AXA {{ $tenant->insurance_policy ?? 'N/A' }}</p>
    </div>
</div>
</body>
</html>

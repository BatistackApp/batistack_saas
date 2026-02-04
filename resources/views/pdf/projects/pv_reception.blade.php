<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PV de Réception - {{ $project->code_project }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'Noto Sans';
            src: url('https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap');
        }
        body { font-family: 'Noto Sans', sans-serif; font-size: 11px; color: #1a202c; }
        .text-blue-batistack { color: #002157; }
        .bg-blue-batistack { background-color: #002157; }
        .border-dashed-b { border-bottom: 1px dashed #cbd5e0; }
    </style>
</head>
<body class="bg-white p-10">
<!-- Header -->
<div class="flex justify-between items-start mb-10">
    <div>
        <div class="text-2xl font-bold text-blue-batistack">BATISTACK</div>
        <p class="text-xs text-gray-500 uppercase">Procès-Verbal de Réception de Travaux</p>
    </div>
    <div class="text-right">
        <p class="font-bold">Réf. Projet : {{ $project->code_project }}</p>
        <p>Date : {{ now()->format('d/m/Y') }}</p>
    </div>
</div>

<!-- Parties Prenantes -->
<div class="grid grid-cols-2 gap-10 mb-10">
    <div>
        <h3 class="font-bold text-blue-batistack border-b mb-2 pb-1">ENTREPRISE (L'Entrepreneur)</h3>
        <p class="font-bold">{{ $tenant->name }}</p>
        <p class="text-gray-600">{{ $tenant->address }}</p>
    </div>
    <div>
        <h3 class="font-bold text-blue-batistack border-b mb-2 pb-1">MAÎTRE D'OUVRAGE (Le Client)</h3>
        <p class="font-bold">{{ $project->customer->name }}</p>
        <p class="text-gray-600">{{ $project->customer->address }}</p>
    </div>
</div>

<!-- Corps du PV -->
<div class="mb-10 text-sm leading-relaxed">
    <p class="mb-4">
        Le Maître d'Ouvrage déclare procéder ce jour à la réception des travaux concernant l'opération suivante :
        <span class="font-bold">{{ $project->name }}</span>,
        exécutés en vertu du marché référencé <span class="font-bold">{{ $project->quote->reference ?? 'N/A' }}</span>.
    </p>
</div>

<!-- Prononcé de la réception -->
<div class="border-2 border-blue-batistack p-6 rounded-lg mb-10">
    <h2 class="text-lg font-bold text-blue-batistack mb-4 uppercase">Décision de réception</h2>

    <div class="space-y-4">
        <div class="flex items-center">
            <div class="w-4 h-4 border border-black mr-2"></div>
            <p>La réception est prononcée **SANS RÉSERVE**.</p>
        </div>
        <div class="flex items-start">
            <div class="w-4 h-4 border border-black mr-2 mt-1"></div>
            <div>
                <p>La réception est prononcée **AVEC RÉSERVES** (détaillées ci-dessous).</p>
                <p class="text-xs text-gray-500 italic">Le Maître d'Ouvrage fixe un délai de ________ jours pour la levée des réserves.</p>
            </div>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 border border-black mr-2"></div>
            <p>La réception est **REFUSÉE** (Motif en annexe).</p>
        </div>
    </div>
</div>

<!-- Zone de Réserves -->
<div class="mb-10">
    <h3 class="font-bold text-blue-batistack mb-2 uppercase">Liste des réserves (le cas échéant) :</h3>
    <div class="space-y-4">
        <div class="h-6 border-dashed-b"></div>
        <div class="h-6 border-dashed-b"></div>
        <div class="h-6 border-dashed-b"></div>
        <div class="h-6 border-dashed-b"></div>
    </div>
</div>

<!-- Signatures -->
<div class="mt-20">
    <div class="grid grid-cols-2 gap-20">
        <div class="text-center">
            <p class="mb-16 font-bold uppercase text-xs">Le Maître d'Ouvrage (Signature)</p>
            <div class="border-t pt-2 italic text-gray-400">Précédée de la mention "Reçu les travaux"</div>
        </div>
        <div class="text-center">
            <p class="mb-16 font-bold uppercase text-xs">L'Entrepreneur (Signature)</p>
            <div class="border-t pt-2 italic text-gray-400">Cachet de l'entreprise</div>
        </div>
    </div>
</div>

<!-- Footer Légal -->
<div class="absolute bottom-10 left-10 right-10 text-[9px] text-gray-400">
    <p>Le présent PV marque le point de départ des garanties légales (GPA, Biennale, Décennale) conformément aux articles 1792 et suivants du Code Civil.</p>
</div>
</body>
</html>

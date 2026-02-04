@extends('pdf.layout')

@section('content')
    <div class="grid grid-cols-2 gap-8 mb-12">
        <div class="border rounded-lg p-4 bg-gray-50">
            <p class="text-xs uppercase text-gray-500 mb-1">Destinataire</p>
            <p class="font-bold text-lg">{{ $quote->customer->name }}</p>
            <p>{{ $quote->customer->address }}</p>
            <p>{{ $quote->customer->zip_code }} {{ $quote->customer->city }}</p>
        </div>
        <div class="border rounded-lg p-4">
            <p class="text-xs uppercase text-gray-500 mb-1">Chantier / Projet</p>
            <p class="font-bold">{{ $quote->project->name }}</p>
            <p class="text-gray-600">{{ $quote->project->code_project }}</p>
        </div>
    </div>

    <!-- Tableau des articles -->
    <table class="w-full mb-8">
        <thead>
        <tr class="bg-blue-batistack text-white text-left">
            <th class="p-3 rounded-tl-lg">Description</th>
            <th class="p-3 text-center">Qté</th>
            <th class="p-3 text-center">Unité</th>
            <th class="p-3 text-right">P.U. HT</th>
            <th class="p-3 text-right rounded-tr-lg">Total HT</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        @foreach($quote->items as $item)
            <tr>
                <td class="p-3">
                    <p class="font-bold">{{ $item->label }}</p>
                    @if($item->description)
                        <p class="text-gray-500 text-xs">{{ $item->description }}</p>
                    @endif
                </td>
                <td class="p-3 text-center">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                <td class="p-3 text-center">{{ $item->unit ?? 'u' }}</td>
                <td class="p-3 text-right">{{ number_format($item->unit_price_ht, 2, ',', ' ') }} €</td>
                <td class="p-3 text-right font-semibold">{{ number_format($item->quantity * $item->unit_price_ht, 2, ',', ' ') }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Utilisation de la page-break si le contenu est trop long (Logique à adapter selon les données) -->
    {{-- <div class="page-break"></div> --}}

    <!-- Totaux -->
    <div class="flex justify-end">
        <div class="w-1/2">
            <div class="flex justify-between p-2">
                <span class="text-gray-600">Total HT</span>
                <span class="font-bold">{{ number_format($quote->total_ht, 2, ',', ' ') }} €</span>
            </div>
            <div class="flex justify-between p-2">
                <span class="text-gray-600">TVA (20%)</span>
                <span class="font-bold">{{ number_format($quote->total_tva, 2, ',', ' ') }} €</span>
            </div>
            <div class="flex justify-between bg-blue-batistack text-white p-3 rounded-lg mt-2">
                <span class="font-bold text-lg uppercase">Total TTC</span>
                <span class="font-bold text-lg">{{ number_format($quote->total_ttc, 2, ',', ' ') }} €</span>
            </div>
        </div>
    </div>
@endsection

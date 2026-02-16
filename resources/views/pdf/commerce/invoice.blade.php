@extends('pdf.layout')

@section('content')
    <!-- Références Projet & Client -->
    <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="border p-4 rounded bg-gray-50">
            <h3 class="text-blue-batistack font-bold border-b mb-2 pb-1 text-xs uppercase">Client</h3>

            <p>{{ $invoice->tiers->address }}</p>
            <p>{{ $invoice->tiers->zip_code }} {{ $invoice->tiers->city }}</p>
        </div>
        <div class="border p-4 rounded">
            <h3 class="text-blue-batistack font-bold border-b mb-2 pb-1 text-xs uppercase">Chantier</h3>
            <p class="font-bold">{{ $invoice->project->name }}</p>
            <p class="text-gray-600">Marché de référence : {{ $invoice->quote->reference ?? 'N/A' }}</p>
            <p class="text-gray-600">Code projet : {{ $invoice->project->code_project }}</p>
        </div>
    </div>

    <!-- Tableau d'avancement BTP détaillé -->
    <table class="mb-8">
        <thead>
        <tr>
            <th class="w-1/3">Désignation des travaux</th>
            <th class="text-right">Montant Marché</th>
            <th class="text-center">Cumul Précédent</th>
            <th class="text-center">Mois (%)</th>
            <th class="text-center">Cumul (%)</th>
            <th class="text-right">Net à Facturer</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $item)
            @php
                $totalLineHt = (float) ($item->quoteItem->unit_price_ht * $item->quoteItem->quantity);
                // Le cumul précédent est calculé en retirant l'avancement de la période (item HT / total marché)
                $periodProgress = $totalLineHt > 0 ? ($item->unit_price_ht / $totalLineHt * 100) : 0;
                $previousPercentage = (float) $item->progress_percentage - $periodProgress;
            @endphp
            <tr>
                <td class="font-medium">
                    {{ $item->label }}
                    <div class="text-[9px] text-gray-500 font-normal">TVA : {{ number_format($item->tax_rate, 1) }}%</div>
                </td>
                <td class="text-right">{{ number_format($totalLineHt, 2, ',', ' ') }} €</td>
                <td class="text-center text-gray-400">{{ number_format($previousPercentage, 2) }}%</td>
                <td class="text-center font-bold text-blue-600">{{ number_format($periodProgress, 2) }}%</td>
                <td class="text-center">{{ number_format($item->progress_percentage, 2) }}%</td>
                <td class="text-right font-bold">{{ number_format($item->unit_price_ht, 2, ',', ' ') }} €</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Décompte financier -->
    <div class="flex justify-end mt-4">
        <div class="w-72 space-y-2">
            <div class="flex justify-between text-gray-700">
                <span>Total de la période HT</span>
                <span class="font-semibold">{{ number_format($invoice->total_ht, 2, ',', ' ') }} €</span>
            </div>

            <div class="flex justify-between text-gray-700">
                <span>Total TVA</span>
                <span>{{ number_format($invoice->total_tva, 2, ',', ' ') }} €</span>
            </div>

            <div class="flex justify-between font-bold border-t pt-2 text-sm">
                <span>TOTAL TTC PÉRIODE</span>
                <span>{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</span>
            </div>

            <!-- Spécificités BTP : Retenues et Garanties -->
            <div class="bg-gray-50 p-3 rounded-lg border mt-4 space-y-2">
                @if($invoice->retenue_garantie_pct > 0)
                    <div class="flex justify-between text-red-600 text-[10px]">
                        <span>Retenue de garantie ({{ number_format($invoice->retenue_garantie_pct, 1) }}%)</span>
                        <span>- {{ number_format($invoice->retenue_garantie_amount, 2, ',', ' ') }} €</span>
                    </div>
                @endif

                @if($invoice->compte_prorata_amount > 0)
                    <div class="flex justify-between text-red-600 text-[10px]">
                        <span>Compte prorata</span>
                        <span>- {{ number_format($invoice->compte_prorata_amount, 2, ',', ' ') }} €</span>
                    </div>
                @endif

                <div class="flex justify-between border-t border-gray-300 pt-2 font-bold text-blue-batistack text-sm uppercase">
                    <span>NET À PAYER TTC</span>
                    <span>{{ number_format($invoice->net_to_pay, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mentions Légales & Pied de page -->
    <div class="mt-16 text-[9px] text-gray-500 leading-relaxed border-t pt-4">
        <div class="grid grid-cols-2 gap-8">
            <div>
                <p><strong>Conditions de règlement :</strong> {{ $invoice->tiers->payment_terms_code ?? 'Paiement à 30 jours net.' }}</p>
                <p><strong>Pénalités de retard :</strong> Taux REFI majoré de 10 points + Indemnité de 40€ (L441-10 C. Com).</p>
                @if($invoice->is_autoliquidation)
                    <p class="font-bold text-blue-800 mt-2 italic">AUTOLIQUIDATION : TVA due par le preneur (Art. 283-2 nonies du CGI).</p>
                @endif
            </div>
            <div class="text-right">
                <p>Bon pour accord et certification du service fait,</p>
                <p class="mt-8 text-gray-300">Signature & Cachet</p>
            </div>
        </div>
    </div>
@endsection

<?php

namespace App\Http\Controllers\Intervention;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intervention\StoreInterventionItemRequest;
use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use App\Models\Intervention\Intervention;
use App\Models\Intervention\InterventionItem;
use Illuminate\Http\JsonResponse;

class InterventionItemController extends Controller
{
    public function store(StoreInterventionItemRequest $request, Intervention $intervention): JsonResponse
    {
        $data = $request->validated();

        // Récupération automatique du coût de revient au moment de l'ajout
        if ($request->filled('article_id')) {
            $article = Article::findOrFail($data['article_id']);
            $data['unit_cost_ht'] = $article->cump_ht;
        } elseif ($request->filled('ouvrage_id')) {
            $ouvrage = Ouvrage::findOrFail($data['ouvrage_id']);
            $data['unit_cost_ht'] = $ouvrage->theoretical_cost;
        }

        // Calcul automatique du total HT de la ligne
        $data['total_ht'] = (float) $data['quantity'] * (float) $data['unit_price_ht'];

        $item = $intervention->items()->create($data);

        return response()->json($item, 201);
    }

    public function destroy(Intervention $intervention, InterventionItem $item): JsonResponse
    {
        // La validation d'autorisation est déjà dans la Request, mais on assure la sécurité ici
        if ($intervention->status !== \App\Enums\Intervention\InterventionStatus::InProgress) {
            return response()->json(['error' => 'Action impossible hors phase d\'exécution.'], 403);
        }

        $item->delete();

        return response()->json(null, 204);
    }
}

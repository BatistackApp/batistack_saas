<?php

namespace App\Services\Articles;

use App\Models\Articles\Ouvrage;
use App\Models\Articles\OuvrageItem;
use Illuminate\Database\Eloquent\Collection;

class OuvrageService
{
    public function __construct(private readonly CalculationService $calculationService) {}

    public function create(array $data): Ouvrage
    {
        $ouvrage = Ouvrage::create($data);

        if (isset($data['items'])) {
            $this->addItems($ouvrage, $data['items']);
        }

        return $ouvrage->refresh();
    }

    public function update(Ouvrage $ouvrage, array $data): Ouvrage
    {
        $ouvrage->update($data);

        if (isset($data['items'])) {
            $ouvrage->items()->delete();
            $this->addItems($ouvrage, $data['items']);
        }

        return $ouvrage->refresh();
    }

    public function addItems(Ouvrage $ouvrage, array $items): void
    {
        foreach ($items as $item) {
            OuvrageItem::create([
                'ouvrage_id' => $ouvrage->id,
                'article_id' => $item['article_id'],
                'quantity' => $item['quantity'],
                'unit_of_measure' => $item['unit_of_measure'],
            ]);
        }
    }

    public function removeItem(OuvrageItem $item): void
    {
        $item->delete();
    }

    public function calculateCost(Ouvrage $ouvrage): float
    {
        return $this->calculationService->calculateOuvrageCost($ouvrage);
    }

    public function getActive(): Collection
    {
        return Ouvrage::whereNull('archived_at')->get();
    }

    public function archive(Ouvrage $ouvrage): Ouvrage
    {
        $ouvrage->update(['archived_at' => now()]);
        return $ouvrage;
    }
}

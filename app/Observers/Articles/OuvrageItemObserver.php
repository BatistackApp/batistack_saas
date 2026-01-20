<?php

namespace App\Observers\Articles;

use App\Models\Articles\OuvrageItem;
use App\Services\Articles\CalculationService;

class OuvrageItemObserver
{
    public function __construct(private readonly CalculationService $calculationService) {}
    public function created(OuvrageItem $item): void
    {
        $this->updateOuvrageCost($item);
    }

    public function updated(OuvrageItem $item): void
    {
        $this->updateOuvrageCost($item);
    }

    public function deleted(OuvrageItem $item): void
    {
        if ($item->ouvrage) {
            $this->updateOuvrageCost($item);
        }
    }

    private function updateOuvrageCost(OuvrageItem $item): void
    {
        $cost = $this->calculationService->calculateOuvrageCost($item->ouvrage);
        $item->ouvrage->update(['cost' => $cost]);
    }
}

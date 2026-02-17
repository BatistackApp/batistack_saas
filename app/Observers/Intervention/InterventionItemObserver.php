<?php

namespace App\Observers\Intervention;

use App\Models\Intervention\InterventionItem;
use App\Services\Intervention\InterventionFinancialService;

class InterventionItemObserver
{
    public function __construct(
        protected InterventionFinancialService $financialService
    ) {}

    public function saved(InterventionItem $item): void
    {
        $this->financialService->refreshValuation($item->intervention);
    }

    public function deleted(InterventionItem $item): void
    {
        $this->financialService->refreshValuation($item->intervention);
    }
}

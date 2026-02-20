<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\PurchaseOrderItem;
use App\Services\Commerce\FinancialCalculatorService;

class PurchaseOrderItemObserver
{
    public function created(PurchaseOrderItem $item): void
    {
        $this->updateParent($item);
    }

    public function saved(PurchaseOrderItem $item): void
    {
        $this->updateParent($item);
    }

    public function deleted(PurchaseOrderItem $item): void
    {
        $this->updateParent($item);
    }

    protected function updateParent(PurchaseOrderItem $item): void
    {
        app(FinancialCalculatorService::class)->updateDocumentTotals($item->purchaseOrder);
    }
}

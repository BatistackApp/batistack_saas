<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\PurchaseOrderItem;

class PurchaseOrderItemObserver
{
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
        $order = $item->purchaseOrder;
        $totals = $order->items()
            ->selectRaw('SUM(quantity * unit_price_ht) as ht, SUM(quantity * unit_price_ht * (tax_rate / 100)) as tva')
            ->first();

        $order->update([
            'total_ht' => (float) $totals->ht,
            'total_tva' => (float) $totals->tva,
        ]);
    }
}

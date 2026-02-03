<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\PurchaseOrder;

class PurchaseOrderObserver
{
    public function creating(PurchaseOrder $order): void
    {
        if (empty($order->reference)) {
            $year = date('Y');
            $count = PurchaseOrder::whereYear('created_at', $year)->count() + 1;
            $order->reference = "BC-{$year}-".str_pad($count, 5, '0', STR_PAD_LEFT);
        }
    }
}

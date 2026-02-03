<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\PurchaseOrder;
use App\Notifications\Commerce\PurchaseOrderValidatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendPurchaseOrderToSupplierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private PurchaseOrder $order) {}

    public function handle(): void
    {
        // Dans une implémentation réelle, on générerait le PDF ici
        // $pdf = PDF::loadView('pdf.purchase_order', ['order' => $this->order]);
        Log::info("Envoi du Bon de Commande {$this->order->reference} au fournisseur {$this->order->supplier->name}.");

        // Notification du fournisseur (si email présent) et du créateur ainsi que de la commande PDF généré
        if ($this->order->supplier->email) {
            // Mail::to($this->order->supplier->email)->send(new PurchaseOrderMailable($this->order));
        }

        // Notification interne
        if ($this->order->createdBy) {
            $this->order->createdBy->notify(new PurchaseOrderValidatedNotification($this->order));
        }
    }
}

<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\PurchaseOrderStatus;
use App\Models\Articles\Warehouse;
use App\Models\Commerce\PurchaseOrder;
use App\Models\Commerce\PurchaseOrderItem;
use App\Services\Articles\StockMovementService;
use DB;
use Exception;

class PurchaseOrderService
{
    public function __construct(private StockMovementService $stockService) {}

    public function validateOrder(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== PurchaseOrderStatus::Draft) {
            throw new Exception('Seule une commande en brouillon peut être validée.');
        }

        $order->update(['status' => PurchaseOrderStatus::Sent]);

        return $order;
    }

    /**
     * Enregistre une réception de marchandise liée à un bon de commande.
     * Met à jour les reliquats et déclenche les entrées en stock.
     */
    public function recordReception(PurchaseOrder $order, Warehouse $warehouse, array $itemsData): void
    {
        DB::transaction(function () use ($order, $warehouse, $itemsData) {
            foreach ($itemsData as $data) {
                $item = PurchaseOrderItem::findOrFail($data['item_id']);
                $qtyReceived = (float) $data['quantity'];

                if ($qtyReceived <= 0) {
                    continue;
                }

                // Mise à jour du reliquat sur la ligne de commande
                $item->increment('received_quantity', $qtyReceived);

                // Déclenchement de l'entrée en stock réelle via le module Inventaire
                if ($item->article) {
                    $this->stockService->recordEntry(
                        $item->article,
                        $warehouse,
                        $qtyReceived,
                        (float) $item->unit_price_ht,
                        ['reference' => $order->reference]
                    );
                }
            }

            $this->updateOrderStatusAfterReception($order);
        });
    }

    /**
     * Met à jour automatiquement le statut de la commande en fonction des réceptions.
     */
    protected function updateOrderStatusAfterReception(PurchaseOrder $order): void
    {
        $order->load('items');

        $totalOrdered = $order->items->sum('quantity');
        $totalReceived = $order->items->sum('received_quantity');

        if ($totalReceived >= $totalOrdered) {
            $order->update(['status' => PurchaseOrderStatus::Received]);
        } elseif ($totalReceived > 0) {
            $order->update(['status' => PurchaseOrderStatus::PartiallyReceived]);
        }
    }
}

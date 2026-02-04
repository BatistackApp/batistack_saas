<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\PurchaseOrderStatus;
use App\Models\Articles\Warehouse;
use App\Models\Commerce\PurchaseOrder;
use App\Models\Commerce\PurchaseOrderItem;
use App\Models\Commerce\PurchaseOrderReception;
use App\Services\Articles\StockMovementService;
use DB;
use Exception;
use Illuminate\Support\Facades\Auth;

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
     * Enregistre une réception de marchandise avec historique complet.
     */
    public function recordReception(PurchaseOrder $order, Warehouse $warehouse, array $itemsData, string $deliveryNoteRef, ?string $receivedAt = null): void
    {
        DB::transaction(function () use ($order, $warehouse, $itemsData, $deliveryNoteRef, $receivedAt) {
            foreach ($itemsData as $data) {
                $item = PurchaseOrderItem::findOrFail($data['item_id']);
                $qtyReceived = (float) $data['quantity'];

                if ($qtyReceived <= 0) {
                    continue;
                }

                // 1. ARCHIVAGE : Enregistrement dans l'historique des réceptions (Audit Trail)
                PurchaseOrderReception::create([
                    'purchase_order_item_id' => $item->id,
                    'quantity' => $qtyReceived,
                    'delivery_note_ref' => $deliveryNoteRef,
                    'received_at' => $receivedAt ?? now()->format('Y-m-d'),
                    'created_by' => Auth::id(),
                ]);

                // 2. DENORMALISATION : Mise à jour du total reçu sur la ligne pour la performance
                $item->increment('received_quantity', $qtyReceived);

                // 3. LOGISTIQUE : Entrée en stock réelle
                if ($item->article) {
                    $this->stockService->recordEntry(
                        $item->article,
                        $warehouse,
                        $qtyReceived,
                        (float) $item->unit_price_ht,
                        [
                            'reference' => $deliveryNoteRef, // On trace le BL dans le stock
                            'notes' => "Réception Commande {$order->reference}",
                        ]
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

        $status = PurchaseOrderStatus::PartiallyReceived;
        if ($totalReceived >= $totalOrdered) {
            $status = PurchaseOrderStatus::Received;
        }

        $order->update(['status' => $status]);
    }
}

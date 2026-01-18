<?php

namespace App\Services\Articles;

use App\Enums\Articles\StockMouvementReason;
use App\Enums\Articles\StockMouvementType;
use App\Models\Articles\Article;
use App\Models\Articles\Stock;
use App\Models\Articles\StockMouvement;
use App\Models\Articles\Warehouse;

class StockService
{
    public function getStock(Article $article, Warehouse $warehouse): Stock
    {
        return Stock::firstOrCreate(
            [
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
            ],
            ['quantity' => 0]
        );
    }

    public function addStock(
        Article $article,
        Warehouse $warehouse,
        float $quantity,
        StockMouvementReason $reason,
        ?string $reference = null
    ): StockMouvement {
        return $this->recordMovement(
            $article,
            $warehouse,
            $quantity,
            StockMouvementType::Entree,
            $reason,
            $reference
        );
    }

    public function removeStock(
        Article $article,
        Warehouse $warehouse,
        float $quantity,
        StockMouvementReason $reason,
        ?string $reference = null
    ): StockMouvement {
        return $this->recordMovement(
            $article,
            $warehouse,
            $quantity,
            StockMouvementType::Sortie,
            $reason,
            $reference
        );
    }

    public function adjustStock(
        Article $article,
        Warehouse $warehouse,
        float $quantity,
        StockMouvementReason $reason,
        ?string $reference = null
    ): StockMouvement {
        return $this->recordMovement(
            $article,
            $warehouse,
            $quantity,
            StockMouvementType::Ajustement,
            $reason,
            $reference
        );
    }

    public function transferStock(
        Article $article,
        Warehouse $from,
        Warehouse $to,
        float $quantity,
        ?string $reference = null
    ): array {
        $exit = $this->recordMovement(
            $article,
            $from,
            $quantity,
            StockMouvementType::Transfert,
            StockMouvementReason::Transfert,
            $reference
        );

        $entry = $this->recordMovement(
            $article,
            $to,
            $quantity,
            StockMouvementType::Transfert,
            StockMouvementReason::Transfert,
            $reference
        );

        return [$exit, $entry];
    }

    public function getMovements(Article $article, ?Warehouse $warehouse = null)
    {
        $query = StockMouvement::where('article_id', $article->id);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->latest()->get();
    }

    public function getWarehouseStocks(Warehouse $warehouse)
    {
        return Stock::where('warehouse_id', $warehouse->id)
            ->where('quantity', '>', 0)
            ->with('article')
            ->get();
    }

    private function recordMovement(
        Article $article,
        Warehouse $warehouse,
        float $quantity,
        StockMouvementType $type,
        StockMouvementReason $reason,
        ?string $reference = null
    ): StockMouvement {
        $stock = $this->getStock($article, $warehouse);

        $movement = StockMouvement::create([
            'article_id' => $article->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $quantity,
            'type' => $type,
            'reason' => $reason,
            'reference' => $reference,
        ]);

        $this->updateStockQuantity($stock, $type, $quantity);

        return $movement;
    }

    private function updateStockQuantity(Stock $stock, StockMouvementType $type, float $quantity): void
    {
        match ($type) {
            StockMouvementType::Entree, StockMouvementType::Transfert => $stock->increment('quantity', $quantity),
            StockMouvementType::Sortie => $stock->decrement('quantity', $quantity),
            StockMouvementType::Ajustement => $stock->update(['quantity' => $stock->quantity + $quantity]),
        };
    }
}

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
    public function getStock(Article $article, Warehouse $warehouse): \Illuminate\Database\Eloquent\Model
    {
        return Stock::where('tenant_id', $article->tenant->id)
            ->firstOrCreate(
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

    /**
     * @throws \Throwable
     */
    public function transferStock(
        Article $article,
        Warehouse $from,
        Warehouse $to,
        float $quantity,
        ?string $reference = null
    ): array {

        return \DB::transaction(function () use ($article, $from, $to, $quantity, $reference) {
            $exit = $this->recordMovement(
                $article,
                $from,
                $quantity,
                StockMouvementType::Sortie,
                StockMouvementReason::Transfert,
                $reference
            );

            $entry = $this->recordMovement(
                $article,
                $to,
                $quantity,
                StockMouvementType::Entree,
                StockMouvementReason::Transfert,
                $reference
            );
            return [$exit, $entry];
        });
    }

    public function getMovements(Article $article, ?Warehouse $warehouse = null): StockMouvement
    {
        $query = StockMouvement::where('article_id', $article->id);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->latest()->get();
    }

    public function getWarehouseStocks(Warehouse $warehouse): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Collection
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

        return StockMouvement::create([
            'tenant_id' => $article->tenant_id,
            'article_id' => $article->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $quantity,
            'type' => $type,
            'reason' => $reason,
            'reference' => $reference,
        ]);
    }
}

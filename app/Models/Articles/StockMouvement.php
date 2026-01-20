<?php

namespace App\Models\Articles;

use App\Enums\Articles\StockMouvementReason;
use App\Enums\Articles\StockMouvementType;
use App\Models\Core\Tenant;
use App\Models\User;
use App\Observers\Articles\StockMouvementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([StockMouvementObserver::class])]
class StockMouvement extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'mouvement_date' => 'timestamp',
            'type' => StockMouvementType::class,
            'reason' => StockMouvementReason::class,
            'quantity' => 'decimal:3',
        ];
    }
}

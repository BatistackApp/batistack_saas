<?php

namespace App\Models\Articles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'theoretical_quantity' => 'decimal:3',
            'counted_quantity' => 'decimal:3',
            'difference' => 'decimal:3',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}

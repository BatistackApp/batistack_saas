<?php

namespace App\Models\Core;

use App\Enums\Core\InvoiceLineItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLineItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected function casts(): array
    {
        return [
            'type' => InvoiceLineItemType::class,
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function calculateTotal(): string
    {
        return (string) (($this->quantity ?? 1) * ($this->unit_price ?? 0));
    }
}

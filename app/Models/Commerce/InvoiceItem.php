<?php

namespace App\Models\Commerce;

use App\Observers\Commerce\InvoiceItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([InvoiceItemObserver::class])]
class InvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function invoices(): BelongsTo
    {
        return $this->belongsTo(Invoices::class);
    }

    public function quoteItem(): BelongsTo
    {
        return $this->belongsTo(QuoteItem::class);
    }
}

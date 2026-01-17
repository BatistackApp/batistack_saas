<?php

namespace App\Models\Core;

use App\Enums\Core\InvoiceStatus;
use App\Observers\Core\InvoiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

#[ObservedBy([InvoiceObserver::class])]
class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'billing_period_start' => 'datetime',
            'billing_period_end' => 'datetime',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', '!=', InvoiceStatus::Paid->value);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_at', '<', now())
            ->where('status', '!=', InvoiceStatus::Paid->value);
    }

    public function isOverdue(): bool
    {
        return $this->due_at?->isPast() && $this->status !== InvoiceStatus::Paid;
    }

    /**
     * Marque la facture comme payée de manière idempotente.
     */
    public function markAsPaid(): void
    {
        try {
            if ($this->status === InvoiceStatus::Paid->value && $this->paid_at !== null) {
                return;
            }

            $this->status = InvoiceStatus::Paid->value;
            if ($this->paid_at === null) {
                $this->paid_at = now();
            }

            $this->save();

            Log::info('Invoice::markAsPaid completed', ['invoice_id' => $this->id]);
        } catch (\Throwable $e) {
            Log::error('Invoice::markAsPaid failed', [
                'invoice_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

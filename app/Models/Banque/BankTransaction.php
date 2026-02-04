<?php

namespace App\Models\Banque;

use App\Enums\Banque\BankTransactionType;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankTransaction extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function casts(): array
    {
        return [
            'type' => BankTransactionType::class,
            'value_date' => 'date',
            'amount' => 'decimal:2',
            'raw_metadata' => 'array',
            'is_reconciled' => 'boolean',
        ];
    }
}

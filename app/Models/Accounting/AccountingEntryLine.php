<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingEntryLine extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(AccountingEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccounts::class);
    }

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }
}

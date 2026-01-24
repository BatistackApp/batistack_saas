<?php

namespace App\Models\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingEntry extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingEntryLine::class);
    }

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'status' => EntryStatus::class,
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
        ];
    }

    public function isBalanced(): bool
    {
        return bccomp($this->total_debit, $this->total_credit, 2) === 0;
    }
}

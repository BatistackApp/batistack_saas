<?php

namespace App\Models\Accounting;

use App\Enums\Accounting\JournalType;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingJournal extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AccountingEntry::class);
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(AccountingSequence::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => JournalType::class,
        ];
    }
}

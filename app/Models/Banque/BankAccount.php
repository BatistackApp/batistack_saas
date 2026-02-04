<?php

namespace App\Models\Banque;

use App\Enums\Banque\BankAccountType;
use App\Enums\Banque\BankSyncStatus;
use App\Observers\BankAccountObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([BankAccountObserver::class])]
class BankAccount extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
            'current_balance' => 'decimal:2',
            'initial_balance' => 'decimal:2',
            'type' => BankAccountType::class,
            'sync_status' => BankSyncStatus::class,
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Helper : Vérifie si le compte nécessite une re-connexion (Consentement Bridge)
     */
    public function needsReauth(): bool
    {
        return $this->sync_status === BankSyncStatus::Error;
    }
}

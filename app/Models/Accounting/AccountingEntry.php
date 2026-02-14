<?php

namespace App\Models\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Core\Tenants;
use App\Models\User;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingEntry extends Model
{
    use HasFactory, SoftDeletes, HasUlids, HasTenant;

    protected $fillable = [
        'ulid',
        'tenants_id',
        'journal_id',
        'reference_number',
        'accounting_date',
        'label',
        'description',
        'total_debit',
        'total_credit',
        'status',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'accounting_date' => 'date',
            'validated_at' => 'datetime',
            'status' => EntryStatus::class,
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AccountingEntryLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function isBalanced(): bool
    {
        return bccomp($this->total_debit, $this->total_credit, 4) === 0;
    }
}

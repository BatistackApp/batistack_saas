<?php

namespace App\Models\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Models\User;
use App\Observers\Expense\ExpenseReportObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ExpenseReportObserver::class])]
class ExpenseReport extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class);
    }

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'validated_at' => 'datetime',
            'status' => ExpenseStatus::class,
            'amount_ht' => 'decimal:2',
            'amount_tva' => 'decimal:2',
            'amount_ttc' => 'decimal:2',
        ];
    }

    /**
     * Vérifie si la note est modifiable (Brouillon ou Rejetée).
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected]);
    }
}

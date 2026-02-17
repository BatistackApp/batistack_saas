<?php

namespace App\Models\Accounting;

use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Observers\Accounting\AccountingEntryLineObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([AccountingEntryLineObserver::class])]
class AccountingEntryLine extends Model
{
    use HasFactory, HasTenant, HasUlids;

    protected $fillable = [
        'ulid',
        'accounting_entry_id',
        'chart_of_account_id',
        'debit',
        'credit',
        'description',
        'line_order',
        'project_id',
        'project_phase_id',
        'tenants_id',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:4',
            'credit' => 'decimal:4',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(AccountingEntry::class, 'accounting_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'project_phase_id');
    }

    public function uniqueIds(): array
    {
        return ['ulid']; // Indique au trait HasUlids d'utiliser 'ulid' et non 'id'
    }
}

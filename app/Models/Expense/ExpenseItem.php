<?php

namespace App\Models\Expense;

use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Observers\Expense\ExpenseItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ExpenseItemObserver::class])]
class ExpenseItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class, 'expense_report_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'project_phase_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'metadata' => 'array',
            'tax_rate' => 'decimal:2',
            'amount_ht' => 'decimal:2',
            'amount_tva' => 'decimal:2',
            'amount_ttc' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'vehicle_power' => 'integer',
            'is_billable' => 'boolean',
            'is_mileage' => 'boolean',
            'is_fixed_allowance' => 'boolean',
        ];
    }

    /**
     * Détermine s'il s'agit d'un frais kilométrique.
     */
    public function isMileage(): bool
    {
        return $this->is_mileage || ($this->category && $this->category->requires_distance);
    }
}

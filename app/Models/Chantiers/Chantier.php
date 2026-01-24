<?php

namespace App\Models\Chantiers;

use App\Enums\Chantiers\ChantierStatus;
use App\Models\Core\Tenant;
use App\Models\HR\EmployeeTimesheetLine;
use App\Models\Tiers\Tiers;
use App\Observers\Chantiers\ChantierObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ChantierObserver::class])]
class Chantier extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ChantierCost::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(ChantierBudget::class);
    }

    public function timesheetLines(): HasMany
    {
        return $this->hasMany(EmployeeTimesheetLine::class);
    }

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ChantierStatus::class,
            'budget_total' => 'decimal:2',
            'total_costs' => 'decimal:2',
        ];
    }

    public function getTotalCostsAttribute(): float
    {
        return (float) $this->total_costs;
    }

    public function getBudgetUsagePercentAttribute(): float
    {
        if ($this->budget_total == 0) {
            return 0;
        }

        return ($this->total_costs / $this->budget_total) * 100;
    }
}

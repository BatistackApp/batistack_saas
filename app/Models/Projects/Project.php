<?php

namespace App\Models\Projects;

use App\Enums\Projects\ProjectAmendmentStatus;
use App\Enums\Projects\ProjectStatus;
use App\Enums\Projects\ProjectSuspensionReason;
use App\Models\Commerce\Invoices;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Observers\Projects\ProjectObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ProjectObserver::class])]
class Project extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'customer_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('order');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function amendments(): HasMany
    {
        return $this->hasMany(ProjectAmendment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ProjectStatusHistory::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoices::class, 'project_id');
    }

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'suspension_reason' => ProjectSuspensionReason::class,
            'planned_start_at' => 'date',
            'planned_end_at' => 'date',
            'actual_start_at' => 'date',
            'actual_end_at' => 'date',
            'initial_budget_ht' => 'decimal:2',
            'budget_labor' => 'decimal:2',
            'budget_materials' => 'decimal:2',
            'budget_subcontracting' => 'decimal:2',
            'budget_site_overheads' => 'decimal:2',
            'allocated_phases_ceiling_ht' => 'decimal:2',
        ];
    }

    // Accesseur pour le Budget de Vente Total (Initial + Avenants Acceptés)
    public function totalSalesBudget(): float
    {
        $amendmentsAmount = $this->amendments()
            ->where('status', ProjectAmendmentStatus::Accepted)
            ->sum('amount_ht');

        return (float) $this->initial_budget_ht + $amendmentsAmount;
    }

    // Accesseur pour le Budget Interne Total (Somme des déboursés)
    public function totalInternalBudget(): float
    {
        return (float) ($this->budget_labor + $this->budget_materials + $this->budget_subcontracting + $this->budget_site_overheads);
    }

    /**
     * Vérifie si l'enveloppe des phases est cohérente avec le plafond
     */
    public function getPhasesBudgetIntegrity(): array
    {
        $allocated = $this->phases()->sum('allocated_budget');

        return [
            'allocated' => (float) $allocated,
            'ceiling' => (float) $this->allocated_phases_ceiling_ht,
            'remaining' => (float) ($this->allocated_phases_ceiling_ht - $allocated),
        ];
    }
}

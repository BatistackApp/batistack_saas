<?php

namespace App\Models\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use App\Observers\Locations\RentalContractObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([RentalContractObserver::class])]
class RentalContract extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenants_id', 'provider_id', 'project_id', 'project_phase_id',
        'reference', 'label', 'extension_count',
        'start_date_planned', 'end_date_planned', 'off_hire_requested_at',
        'actual_pickup_at', 'actual_return_at',
        'status', 'notes',
        'delivery_cost_ht', 'return_cost_ht', 'cleaning_fees_ht', 'refuel_fees_ht',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'provider_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RentalAssignment::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'project_phase_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(RentalInspection::class);
    }

    protected function casts(): array
    {
        return [
            'status' => RentalStatus::class,
            'start_date_planned' => 'date',
            'end_date_planned' => 'date',
            'off_hire_requested_at' => 'datetime',
            'actual_pickup_at' => 'datetime',
            'actual_return_at' => 'datetime',
            'delivery_cost_ht' => 'decimal:2',
            'return_cost_ht' => 'decimal:2',
            'cleaning_fees_ht' => 'decimal:2',
            'refuel_fees_ht' => 'decimal:2',
        ];
    }

    /**
     * Accesseur pour obtenir le projet actuellement facturé
     * (Soit le projet du contrat, soit la dernière affectation en cours)
     */
    public function getCurrentProjectAttribute()
    {
        $lastAssignment = $this->assignments()->whereNull('released_at')->latest()->first();
        return $lastAssignment ? $lastAssignment->project : $this->project;
    }
}

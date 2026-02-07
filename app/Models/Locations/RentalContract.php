<?php

namespace App\Models\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\Core\Tenants;
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
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenants_id', 'provider_id', 'project_id', 'project_phase_id',
        'reference', 'label', 'start_date_planned', 'end_date_planned',
        'actual_pickup_at', 'actual_return_at', 'status', 'notes'
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'provider_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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
            'actual_pickup_at' => 'datetime',
            'actual_return_at' => 'datetime',
        ];
    }
}

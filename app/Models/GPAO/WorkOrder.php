<?php

namespace App\Models\GPAO;

use App\Enums\GPAO\WorkOrderStatus;
use App\Models\Articles\Ouvrage;
use App\Models\Articles\Warehouse;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Observers\GPAO\WorkOrderObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([WorkOrderObserver::class])]
class WorkOrder extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'ouvrage_id', 'warehouse_id', 'project_id', 'project_phase_id',
        'reference', 'quantity_planned', 'quantity_produced', 'status',
        'priority', 'planned_start_at', 'planned_end_at', 'actual_start_at', 'actual_end_at',
        'total_cost_ht',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorkOrderStatus::class,
            'quantity_planned' => 'decimal:3',
            'quantity_produced' => 'decimal:3',
            'planned_start_at' => 'datetime',
            'planned_end_at' => 'datetime',
            'total_cost_ht' => 'decimal:2',
        ];
    }

    public function ouvrage(): BelongsTo
    {
        return $this->belongsTo(Ouvrage::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'project_phase_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(WorkOrderOperation::class)->orderBy('sequence');
    }

    public function components(): HasMany
    {
        return $this->hasMany(WorkOrderComponent::class);
    }
}

<?php

namespace App\Models\Projects;

use App\Enums\Projects\ProjectPhaseStatus;
use App\Observers\Projects\ProjectPhaseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ProjectPhaseObserver::class])]
class ProjectPhase extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'budget_allocated_ht' => 'decimal:2',
        'status' => ProjectPhaseStatus::class,
        'progress_percentage' => 'decimal:2',
        'allocated_budget' => 'decimal:2',
        'rad_labor' => 'decimal:2',
        'rad_materials' => 'decimal:2',
        'rad_subcontracting' => 'decimal:2'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'depends_on_phase_id');
    }

    public function totalRad(): float {
        return (float) ($this->rad_labor + $this->rad_materials + $this->rad_subcontracting);
    }
}

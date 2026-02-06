<?php

namespace App\Models\Intervention;

use App\Enums\Intervention\BillingType;
use App\Enums\Intervention\InterventionStatus;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intervention extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'customer_id');
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

    public function items(): HasMany
    {
        return $this->hasMany(InterventionItem::class);
    }

    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'intervention_technician')
            ->withPivot('hours_spent')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'status' => InterventionStatus::class,
            'billing_type' => BillingType::class,
            'planned_at' => 'datetime',
            'total_ht' => 'decimal:2',
            'total_cost_ht' => 'decimal:2',
            'margin_ht' => 'decimal:2',
        ];
    }
}

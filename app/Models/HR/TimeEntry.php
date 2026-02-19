<?php

namespace App\Models\HR;

use App\Enums\HR\TimeEntryStatus;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use App\Observers\HR\TimeEntryObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TimeEntryObserver::class])]
class TimeEntry extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Historique des changements d'état
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TimeEntryLog::class)->latest();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'has_meal_allowance' => 'boolean',
            'has_host_allowance' => 'boolean',
            'hours' => 'decimal:2',
            'status' => TimeEntryStatus::class,
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'valuation_amount' => 'decimal:2',
        ];
    }
}

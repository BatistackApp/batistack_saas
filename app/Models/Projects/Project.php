<?php

namespace App\Models\Projects;

use App\Enums\Projects\ProjectStatus;
use App\Models\Core\Tenants;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenants_id', 'client_id', 'code_project', 'name',
        'description', 'address', 'latitude', 'longitude',
        'budget_initial_ht', 'status', 'planned_start_at',
        'planned_end_at', 'actual_start_at', 'actual_end_at'
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'planned_start_at' => 'date',
        'planned_end_at' => 'date',
        'actual_start_at' => 'date',
        'actual_end_at' => 'date',
        'budget_initial_ht' => 'decimal:2',
    ];

    public function tenants(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

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

    protected function casts(): array
    {
        return [
            'planned_start_at' => 'date',
            'planned_end_at' => 'date',
            'actual_start_at' => 'date',
            'actual_end_at' => 'date',
        ];
    }
}

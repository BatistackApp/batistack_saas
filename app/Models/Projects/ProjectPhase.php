<?php

namespace App\Models\Projects;

use App\Enums\Projects\ProjectPhaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPhase extends Model
{
    use HasFactory;
    protected $fillable = ['project_id', 'name', 'budget_allocated_ht', 'order', 'status'];

    protected $casts = [
        'budget_allocated_ht' => 'decimal:2',
        'status' => ProjectPhaseStatus::class,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

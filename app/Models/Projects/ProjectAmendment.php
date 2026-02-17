<?php

namespace App\Models\Projects;

use App\Enums\Projects\ProjectAmendmentStatus;
use App\Observers\Projects\ProjectAmendmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ProjectAmendmentObserver::class])]
class ProjectAmendment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => ProjectAmendmentStatus::class,
            'amount_ht' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

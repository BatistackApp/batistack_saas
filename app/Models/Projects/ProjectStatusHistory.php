<?php

namespace App\Models\Projects;

use App\Enums\Projects\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStatusHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
            'old_status' => ProjectStatus::class,
            'new_status' => ProjectStatus::class,
        ];
    }
}

<?php

namespace App\Models\Bim;

use App\Enums\Bim\BimModelStatus;
use App\Models\Projects\Project;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BimModel extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenants_id',
        'project_id',
        'name',
        'file_path',
        'version',
        'status',
        'file_size',
        'metadata',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(BimObject::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(BimView::class);
    }

    protected function casts(): array
    {
        return [
            'status' => BimModelStatus::class,
            'metadata' => 'array',
        ];
    }
}

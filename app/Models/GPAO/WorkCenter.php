<?php

namespace App\Models\GPAO;

use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCenter extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'capacity_per_day',
        'hourly_rate', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'capacity_per_day' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function operations(): HasMany
    {
        return $this->hasMany(WorkOrderOperation::class);
    }
}

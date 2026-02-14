<?php

namespace App\Models\Pilotage;

use App\Enums\Pilotage\KpiCategory;
use App\Enums\Pilotage\KpiUnit;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiIndicator extends Model
{
    use HasFactory, SoftDeletes, HasTenant, HasUlids;

    protected $fillable = [
        'tenants_id', 'code', 'name', 'description',
        'category', 'unit', 'formula_class', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'category' => KpiCategory::class,
            'unit' => KpiUnit::class,
            'is_active' => 'boolean',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(KpiSnapshot::class);
    }

    public function thresholds(): HasMany
    {
        return $this->hasMany(KpiThresholds::class);
    }
}

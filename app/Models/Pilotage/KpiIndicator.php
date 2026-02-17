<?php

namespace App\Models\Pilotage;

use App\Enums\Pilotage\KpiCategory;
use App\Enums\Pilotage\KpiUnit;
use App\Observers\Pilotage\KpiIndicatorObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([KpiIndicatorObserver::class])]
class KpiIndicator extends Model
{
    use HasFactory, HasTenant, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenants_id', 'code', 'name', 'description',
        'category', 'unit', 'formula_class', 'is_active',
        'id',
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

    public function uniqueIds(): array
    {
        return ['ulid']; // Indique au trait HasUlids d'utiliser 'ulid' et non 'id'
    }
}

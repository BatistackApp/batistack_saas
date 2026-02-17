<?php

namespace App\Models\Pilotage;

use App\Enums\Pilotage\ThresholdSeverity;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiThresholds extends Model
{
    use HasFactory, HasTenant, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenants_id', 'kpi_indicator_id', 'min_value',
        'max_value', 'severity', 'is_notifiable',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(KpiIndicator::class, 'kpi_indicator_id');
    }

    protected function casts(): array
    {
        return [
            'severity' => ThresholdSeverity::class,
            'is_notifiable' => 'boolean',
            'min_value' => 'float',
            'max_value' => 'float',
        ];
    }

    public function uniqueIds(): array
    {
        return ['ulid']; // Indique au trait HasUlids d'utiliser 'ulid' et non 'id'
    }
}

<?php

namespace App\Models\Pilotage;

use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiSnapshot extends Model
{
    use HasFactory, SoftDeletes, HasUlids, HasTenant;

    protected $fillable = [
        'tenants_id', 'kpi_indicator_id', 'value',
        'measured_at', 'context_type', 'context_id'
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(KpiIndicator::class, 'kpi_indicator_id');
    }

    public function context(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'measured_at' => 'datetime',
            'value' => 'decimal:4',
        ];
    }

    public function uniqueIds(): array
    {
        return ['ulid']; // Indique au trait HasUlids d'utiliser 'ulid' et non 'id'
    }
}

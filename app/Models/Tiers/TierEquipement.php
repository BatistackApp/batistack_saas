<?php

namespace App\Models\Tiers;

use App\Models\Core\Tenants;
use App\Models\Intervention\Intervention;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TierEquipement extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenants_id',
        'customer_id',
        'name',
        'brand',
        'model',
        'serial_number',
        'installation_date',
        'warranty_expiration_date',
        'technical_data',
        'location_details',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'customer_id');
    }

    /**
     * Historique complet des interventions sur cet équipement
     */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'customer_equipment_id');
    }

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'warranty_expiration_date' => 'date',
            'technical_data' => 'array',
        ];
    }
}

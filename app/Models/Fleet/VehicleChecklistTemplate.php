<?php

namespace App\Models\Fleet;

use App\Enums\Fleet\VehicleType;
use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleChecklistTemplate extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenants_id',
        'name',
        'vehicle_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'is_active' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(VehicleChecklistQuestion::class, 'template_id')->orderBy('sort_order');
    }
}

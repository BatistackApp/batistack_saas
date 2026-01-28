<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleCatalog extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tenantModules(): HasMany {
        return $this->hasMany(TenantModule::class, 'module_id');
    }

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}

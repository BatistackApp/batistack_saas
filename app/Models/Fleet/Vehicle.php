<?php

namespace App\Models\Fleet;

use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    public function tenants(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'last_external_sync_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}

<?php

namespace App\Models\Locations;

use App\Models\Projects\Project;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalAssignment extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenants_id',
        'rental_contract_id',
        'project_id',
        'assigned_at',
        'released_at',
        'notes',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class, 'rental_contract_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }
}

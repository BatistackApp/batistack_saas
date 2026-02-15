<?php

namespace App\Models\Fleet;

use App\Enums\Fleet\DesignationStatus;
use App\Enums\Fleet\FinesStatus;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\User;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleFine extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenants_id',
        'vehicle_id',
        'fine_category_id',
        'user_id',
        'notice_number',
        'offense_at',
        'location',
        'amount_initial',
        'amount_discounted',
        'amount_increased',
        'due_date',
        'status',
        'designation_status',
        'project_id',
        'is_project_imputable',
        'document_path',
        'notes',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FineCategory::class, 'fine_category_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected function casts(): array
    {
        return [
            'status' => FinesStatus::class,
            'designation_status' => DesignationStatus::class,
            'offense_at' => 'datetime',
            'due_date' => 'date',
            'is_project_imputable' => 'boolean',
            'amount_initial' => 'decimal:2',
            'amount_discounted' => 'decimal:2',
            'amount_increased' => 'decimal:2',
        ];
    }

    public function daysBeforeIncrease(): int
    {
        return (int) now()->diffInDays($this->due_date, false);
    }
}

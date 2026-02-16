<?php

namespace App\Models\HR;

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\AbsenceType;
use App\Models\Core\Tenants;
use App\Models\User;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsenceRequest extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenants_id',
        'employee_id',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'duration_days',
        'reason',
        'justification_path',
        'validated_by',
        'validated_at',
        'rejection_reason',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    protected function casts(): array
    {
        return [
            'type' => AbsenceType::class,
            'status' => AbsenceRequestStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration_days' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Scope pour les demandes validées (impactant le planning)
     */
    public function scopeApproved($query)
    {
        return $query->where('status', AbsenceRequestStatus::Approved);
    }
}

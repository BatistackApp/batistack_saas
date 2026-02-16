<?php

namespace App\Models\HR;

use App\Observers\HR\EmployeeSkillObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([EmployeeSkillObserver::class])]
class EmployeeSkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'skill_id',
        'issue_date',
        'expiry_date',
        'reference_number',
        'document_path',
        'level',
        'notes',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    /**
     * Détermine si la certification est expirée
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date->isPast();
    }

    /**
     * Détermine si l'expiration est proche (moins de 30 jours)
     */
    public function expiresSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date->isFuture() && $this->expiry_date->diffInDays(now()) <= $days;
    }
}

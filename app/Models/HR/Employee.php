<?php

namespace App\Models\HR;

use App\Enums\Payroll\BtpTravelZone;
use App\Models\Payroll\Payslip;
use App\Models\User;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    protected function casts(): array
    {
        return [
            'hired_at' => 'date',
            'is_active' => 'boolean',
            'hourly_cost_charged' => 'decimal:2',
            'monthly_base_salary' => 'decimal:2',
            'btp_travel_zone' => BtpTravelZone::class,
            'is_long_distance_travel' => 'boolean',
        ];
    }

    public function activeCertifications(): HasMany
    {
        return $this->skills()->whereHas('skill', function ($query) {
            $query->where('type', '!=', \App\Enums\HR\SkillType::HardSkill);
        })->where(function ($query) {
            $query->whereNull('expiry_date')->orWhere('expiry_date', '>', now());
        });
    }

    /**
     * Calcul du taux horaire contractuel basé sur le salaire mensuel (ex: 151.67h)
     */
    public function getContractualHourlyRateAttribute(): float
    {
        return round($this->monthly_base_salary / 151.67, 4);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}

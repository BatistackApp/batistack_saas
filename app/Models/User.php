<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Expense\ExpenseReport;
use App\Models\HR\Employee;
use App\Models\Tiers\TierQualification;
use App\Models\Tiers\Tiers;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasTenant, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenants_id',
        'tiers_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isTenantAdmin(): bool
    {
        return $this->hasRole('tenant_admin');
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ExpenseReport::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Vérifie si l'utilisateur possède une habilitation spécifique valide.
     *
     * * @param string $certificationType Le libellé de la qualification (ex: CACES R482-A)
     */
    public function hasValidQualification(string $certificationType): bool
    {
        if (! $this->tiers_id) {
            return false;
        }

        return TierQualification::where('tiers_id', $this->tiers_id)
            ->where('label', $certificationType)
            ->where('valid_until', '>=', now())
            ->exists();
    }
}

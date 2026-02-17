<?php

namespace App\Models\HR;

use App\Enums\HR\SkillType;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenants_id',
        'name',
        'type',
        'description',
        'requires_expiry',
    ];

    protected function casts(): array
    {
        return [
            'requires_expiry' => 'boolean',
            'type' => SkillType::class,
        ];
    }

    public function employeeSkills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }
}

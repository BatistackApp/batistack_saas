<?php

namespace App\Models\HR;

use App\Models\Core\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(EmployeeTimesheet::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(EmployeeRate::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'resignation_date' => 'date',
        ];
    }
}

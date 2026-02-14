<?php

namespace App\Models\Accounting;

use App\Models\Core\Tenants;
use App\Models\User;
use App\Observers\Accounting\PeriodClosureObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PeriodClosureObserver::class])]
class PeriodClosure extends Model
{
    use HasFactory, SoftDeletes, HasUlids, HasTenant;

    protected $fillable = [
        'ulid',
        'tenants_id',
        'month',
        'year',
        'period_start',
        'period_end',
        'is_locked',
        'closed_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'is_locked' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function uniqueIds(): array
    {
        return ['ulid']; // Indique au trait HasUlids d'utiliser 'ulid' et non 'id'
    }
}

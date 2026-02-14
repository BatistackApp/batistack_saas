<?php

namespace App\Models\Accounting;

use App\Enums\Accounting\AccountType;
use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory, SoftDeletes, HasUlids, HasTenant;

    protected $fillable = [
        'tenants_id',
        'account_number',
        'account_label',
        'nature',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nature' => AccountType::class,
            'is_active' => 'boolean',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AccountingEntryLine::class, 'chart_of_account_id');
    }

    public function uniqueIds(): array
    {
        return ['ulid']; // Indique au trait HasUlids d'utiliser 'ulid' et non 'id'
    }
}

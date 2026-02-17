<?php

namespace App\Models\Accounting;

use App\Enums\Accounting\JournalType;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use HasFactory, HasTenant, HasUlids, SoftDeletes;

    protected $fillable = [
        'ulid',
        'tenants_id',
        'code',
        'label',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => JournalType::class,
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AccountingEntry::class);
    }

    public function uniqueIds(): array
    {
        return ['ulid']; // Indique au trait HasUlids d'utiliser 'ulid' et non 'id'
    }
}

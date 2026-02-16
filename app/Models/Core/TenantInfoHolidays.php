<?php

namespace App\Models\Core;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInfoHolidays extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenants_id',
        'date',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}

<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tenants(): BelongsTo
    {
        return $this->belongsTo(Tenants::class, 'tenants_id');
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'amount_charged' => 'decimal:2',
        ];
    }
}

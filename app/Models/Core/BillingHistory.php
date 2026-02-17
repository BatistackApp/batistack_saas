<?php

namespace App\Models\Core;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingHistory extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'amount_charged' => 'decimal:2',
        ];
    }
}

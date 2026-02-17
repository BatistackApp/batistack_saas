<?php

namespace App\Models\Expense;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseMileageScale extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'active_year' => 'integer',
        ];
    }
}

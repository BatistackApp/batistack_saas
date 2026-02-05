<?php

namespace App\Models\Expense;

use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory, HasTenant;

    protected function casts(): array
    {
        return [
            'requires_distance' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'expense_category_id');
    }
}

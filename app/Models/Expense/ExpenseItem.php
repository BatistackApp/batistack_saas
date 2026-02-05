<?php

namespace App\Models\Expense;

use App\Models\Projects\Project;
use App\Observers\Expense\ExpenseItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ExpenseItemObserver::class])]
class ExpenseItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'metadata' => 'array',
            'tax_rate' => 'decimal:2'
        ];
    }
}
